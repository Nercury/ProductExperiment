<?php

namespace Evispa\ResourceApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author nerijus
 */
class UnicornCompiler implements CompilerPassInterface
{
    private function getBackendServices(ContainerBuilder $container) {
        $services = array();

        foreach ($container->findTaggedServiceIds('resource_backend_config') as $id => $attributes) {
            $taggedDefinition = $container->getDefinition($id);
            $arguments = $taggedDefinition->getArguments();
            $calls = $taggedDefinition->getMethodCalls();

            $primaryBackendId = null;
            $secondaryBackendId = null;
            foreach ($calls as $call) {
                if ('setPrimaryBackend' === $call[0]) {
                    $reference = $call[1][0];
                    $primaryBackendId = (string)$reference;
                } elseif ('setSecondaryBackend' === $call[0]) {
                    $reference = $call[1][0];
                    $secondaryBackendId = (string)$reference;
                }
            }

            if (null === $primaryBackendId && null === $secondaryBackendId) {
                throw new \Evispa\ResourceApiBundle\Exception\BackendConfigurationException(
                    'Backend "'.$arguments[0].'" configuration should have one backend (either primary or secondary). None found.'
                );
            }

            if (null !== $primaryBackendId && null !== $secondaryBackendId) {
                throw new \Evispa\ResourceApiBundle\Exception\BackendConfigurationException(
                    'Backend "'.$arguments[0].'" configuration should have one backend (either primary or secondary). Both found.'
                );
            }

            $backendId = $primaryBackendId;
            if (null === $backendId) {
                $backendId = $secondaryBackendId;
            }

            $services[$arguments[0]] = $backendId;
        }

        return $services;
    }

    private function getManagedPartClasses($backendConfigInfo, $backendConfigs) {
        $managedPartClasses = array();
        foreach ($backendConfigInfo->getManagedParts() as $partName) {
            $definedParts = $backendConfigs->getBackendConfig($backendConfigInfo->getId())->getParts();
            $partClasses = $definedParts[$partName];
            if (!is_array($partClasses)) {
                throw new \Evispa\ResourceApiBundle\Exception\BackendConfigurationException(
                    'The value of "'.$partName.'" in "'.$backendConfigInfo->getId().'" backend '.
                    'definition should be a collection.'
                );
            }
            
            $classesMap = array();
            foreach ($definedParts[$partName] as $className) {
                $classesMap[$className] = true;
            }

            $managedPartClasses[$partName] = $classesMap;
        }
        
        return $managedPartClasses;
    }
    
    private function getUnicornDefinition(ContainerBuilder $container, $apiConfig, $backendServices) {
        $backendConfigs = $container->get('evispa_resource_api.backend_config_registry');
        $appApiBackendMap = $container->getParameter('evispa_resource_api_backend_map');
        $unicornResolver = new \Evispa\ResourceApiBundle\Unicorn\ApiUnicornResolver();

        $resolvedUnicornInfo = $unicornResolver->getUnicornConfigurationInfo($apiConfig, $backendConfigs, $appApiBackendMap);

        $unicornDef = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Unicorn\Unicorn');

        // primary

        $primaryBackendConfigInfo = $resolvedUnicornInfo->getPrimaryBackendConfigInfo();

        $managedPartClasses = $this->getManagedPartClasses($primaryBackendConfigInfo, $backendConfigs);
        
        $unicornPrimaryBackendDef = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend');
        $unicornPrimaryBackendDef->addArgument($primaryBackendConfigInfo->getId());
        $unicornPrimaryBackendDef->addArgument($managedPartClasses);
        $unicornPrimaryBackendDef->addArgument(new \Symfony\Component\DependencyInjection\Reference($backendServices[$primaryBackendConfigInfo->getId()]));

        $unicornDef->addArgument($unicornPrimaryBackendDef);

        // secondary

        $secondaryBackendArray = array();

        $secondaryBackendConfigInfos = $resolvedUnicornInfo->getSecondaryBackendConfigInfos();
        foreach ($secondaryBackendConfigInfos as $secondaryBackendConfigInfo) {
            $managedPartClasses = $this->getManagedPartClasses($secondaryBackendConfigInfo, $backendConfigs);
            
            $unicornSecondaryBackendDef = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Unicorn\UnicornSecondaryBackend');
            $unicornSecondaryBackendDef->addArgument($secondaryBackendConfigInfo->getId());
            $unicornSecondaryBackendDef->addArgument($managedPartClasses);
            $unicornSecondaryBackendDef->addArgument(new \Symfony\Component\DependencyInjection\Reference($backendServices[$secondaryBackendConfigInfo->getId()]));

            $secondaryBackendArray[] = $unicornSecondaryBackendDef;
        }

        $unicornDef->addArgument($secondaryBackendArray);

        return $unicornDef;
    }

    public function process(ContainerBuilder $container)
    {
        //$container->getDefinition('evispa_resource_api.api_config_registry');
        $apiConfigs = $container->get('evispa_resource_api.api_config_registry');
        $backendServices = $this->getBackendServices($container);

        foreach ($apiConfigs->getApiConfigs() as $apiConfig) {
            $versionReader = new \Evispa\ObjectMigration\VersionReader(new \Doctrine\Common\Annotations\AnnotationReader());
            $versionPathSearch = new \Evispa\ObjectMigration\VersionPath\VersionPathSearch($versionReader);

            $resourceClassName = $apiConfig->getResourceClass()->getName();

            $resourceDef = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Manager\ResourceManager');
            $resourceDef->addArgument($resourceClassName);

            $inputMigrationPaths = array();
            $inputVersions = $versionReader->getAllowedClassInputVersions($resourceClassName);
            $inputMigrations = array();

            foreach ($inputVersions as $inputVersion => $inputClass) {
                $pathMethods = $versionPathSearch->find($inputClass, $resourceClassName);
                $migrationPath = array();

                foreach ($pathMethods as $methodInfo) {
                    $serializedAction = \Evispa\ObjectMigration\Action\ActionSerializer::serializeAction($methodInfo->action);
                    $migrationPath[] = $serializedAction;
                }

                $inputMigrationPaths[$inputClass][] = $migrationPath;
            }

            $outputMigrationPaths = array();
            $outputVersions = $versionReader->getAllowedClassOutputVersions($resourceClassName);
            $outputMigrations = array();

            foreach ($outputVersions as $outputVersion => $outputClass) {
                $pathMethods = $versionPathSearch->find($resourceClassName, $outputClass);
                $migrationPath = array();

                foreach ($pathMethods as $methodInfo) {
                    $serializedAction = \Evispa\ObjectMigration\Action\ActionSerializer::serializeAction($methodInfo->action);
                    $migrationPath[] = $serializedAction;
                }

                $outputMigrationPaths[$outputClass][] = $migrationPath;
            }

            $resourceDef->addArgument($inputVersions);
            $resourceDef->addArgument($inputMigrationPaths);
            $resourceDef->addArgument($outputVersions);
            $resourceDef->addArgument($outputMigrationPaths);

            $unicornRef = $this->getUnicornDefinition($container, $apiConfig, $backendServices);
            $resourceDef->addArgument($unicornRef);

            $container->addDefinitions(array('resource.product' => $resourceDef));

            //$unicornInfo = $unicornResolver->makeUnicorn($apiConfig, $backendConfigs, $appApiBackendMap);

            //var_dump($unicornInfo);
        }

        /*$def = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend');
        $def->setPublic(false);
        $def->addArgument($def)*/

        //$container->addDefinitions(array('testa.product' => $def));

        //$container->addClassResource(new \ReflectionClass( 'Evispa\ResourceApiBundle\Registry\ManagerRegistry'));

        /*var_dump($apiConfig->getApiConfigs());
        var_dump($container->get('evispa_resource_api.backend_config_registry')->getBackendConfigs());

        die;*/
    }
}