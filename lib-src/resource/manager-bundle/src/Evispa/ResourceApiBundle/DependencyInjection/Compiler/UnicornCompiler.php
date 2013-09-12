<?php

namespace Evispa\ResourceApiBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\VersionPath\VersionPathSearch;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ResourceApiBundle\Exception\BackendConfigurationException;
use Evispa\ResourceApiBundle\Migration\ClassMigrationBaker;
use Evispa\ResourceApiBundle\Unicorn\ApiUnicornResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
                throw new BackendConfigurationException(
                    'Backend "'.$arguments[0].'" configuration should have one backend (either primary or secondary). None found.'
                );
            }

            if (null !== $primaryBackendId && null !== $secondaryBackendId) {
                throw new BackendConfigurationException(
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
                throw new BackendConfigurationException(
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

    /**
     * @return \Evispa\ResourceApiBundle\Unicorn\Config\UnicornConfigInfo
     */
    private function resolveUnicornInfo(ContainerBuilder $container, $apiConfig, $backendConfigs) {
        $appApiBackendMap = $container->getParameter('evispa_resource_api_backend_map');
        $unicornResolver = new ApiUnicornResolver();
        $resolvedUnicornInfo = $unicornResolver->getUnicornConfigurationInfo($apiConfig, $backendConfigs, $appApiBackendMap);
        return $resolvedUnicornInfo;
    }
    
    private function getUnicornDefinition(ContainerBuilder $container, $resolvedUnicornInfo, $backendServices, $backendConfigs) {
        $unicornDef = new Definition('Evispa\ResourceApiBundle\Unicorn\Unicorn');

        // primary

        $primaryBackendConfigInfo = $resolvedUnicornInfo->getPrimaryBackendConfigInfo();

        $managedPartClasses = $this->getManagedPartClasses($primaryBackendConfigInfo, $backendConfigs);
        
        $unicornPrimaryBackendDef = new Definition('Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend');
        $unicornPrimaryBackendDef->addArgument($primaryBackendConfigInfo->getId());
        $unicornPrimaryBackendDef->addArgument($managedPartClasses);
        $unicornPrimaryBackendDef->addArgument(new Reference($backendServices[$primaryBackendConfigInfo->getId()]));

        $unicornDef->addArgument($unicornPrimaryBackendDef);

        // secondary

        $secondaryBackendArray = array();

        $secondaryBackendConfigInfos = $resolvedUnicornInfo->getSecondaryBackendConfigInfos();
        foreach ($secondaryBackendConfigInfos as $secondaryBackendConfigInfo) {
            $managedPartClasses = $this->getManagedPartClasses($secondaryBackendConfigInfo, $backendConfigs);

            $unicornSecondaryBackendDef = new Definition('Evispa\ResourceApiBundle\Unicorn\UnicornSecondaryBackend');
            $unicornSecondaryBackendDef->addArgument($secondaryBackendConfigInfo->getId());
            $unicornSecondaryBackendDef->addArgument($managedPartClasses);
            $unicornSecondaryBackendDef->addArgument(new Reference($backendServices[$secondaryBackendConfigInfo->getId()]));

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

        $versionReader = new VersionReader(new AnnotationReader());
        
        foreach ($apiConfigs->getApiConfigs() as $apiConfig) {
            $resourceClassName = $apiConfig->getResourceClass()->getName();

            $resourceDef = new Definition('Evispa\ResourceApiBundle\Manager\ResourceManager');
            $resourceDef->addArgument($resourceClassName);

            $migrationInfoBaker = new ClassMigrationBaker();
            $migrationInfo = $migrationInfoBaker->bakeMigrationInfo($versionReader, $resourceClassName);
            
            $resourceDef->addArgument($migrationInfoBaker->bakeServiceDefinition($migrationInfo));

            $unicornDriverId = 'resource_api.'.$apiConfig->getResourceId().'.unicorn';
            
            $backendConfigs = $container->get('evispa_resource_api.backend_config_registry');
            $resolvedUnicornInfo = $this->resolveUnicornInfo($container, $apiConfig, $backendConfigs);
            
            $unicornDef = $this->getUnicornDefinition($container, $resolvedUnicornInfo, $backendServices, $backendConfigs);
            $unicornDef->setLazy(true);
            $container->addDefinitions(array($unicornDriverId => $unicornDef));
            
            $resourceDef->addArgument(new Reference($unicornDriverId));

            $container->addDefinitions(array('resource_api.'.$apiConfig->getResourceId() => $resourceDef));
        }
    }
}