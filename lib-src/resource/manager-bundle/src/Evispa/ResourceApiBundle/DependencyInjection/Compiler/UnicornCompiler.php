<?php

namespace Evispa\ResourceApiBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
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

    private function getManagedPartClasses($backendConfigInfo, $backendConfigs, $partMigrationInfos) {
        $migrationInfoBaker = new ClassMigrationBaker();
        
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

            $managedPartClasses[$partName] = $migrationInfoBaker->bakeServiceDefinition($partMigrationInfos[$partName]);
        }
        
        return $managedPartClasses;
    }

    /**
     * @return UnicornConfigInfo
     */
    private function resolveUnicornInfo(ContainerBuilder $container, $apiConfig, $backendConfigs) {
        $appApiBackendMap = $container->getParameter('evispa_resource_api_backend_map');
        $unicornResolver = new ApiUnicornResolver();
        $resolvedUnicornInfo = $unicornResolver->getUnicornConfigurationInfo($apiConfig, $backendConfigs, $appApiBackendMap);
        return $resolvedUnicornInfo;
    }
    
    private function getUnicornDefinition(ContainerBuilder $container, $resolvedUnicornInfo, $partMigrationInfos, $backendServices, $backendConfigs) {
        $unicornDef = new Definition('Evispa\ResourceApiBundle\Unicorn\Unicorn');

        // primary

        $primaryBackendConfigInfo = $resolvedUnicornInfo->getPrimaryBackendConfigInfo();

        $managedPartClasses = $this->getManagedPartClasses($primaryBackendConfigInfo, $backendConfigs, $partMigrationInfos);
        
        $unicornPrimaryBackendDef = new Definition('Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend');
        $unicornPrimaryBackendDef->addArgument($primaryBackendConfigInfo->getId());
        $unicornPrimaryBackendDef->addArgument($managedPartClasses);
        $unicornPrimaryBackendDef->addArgument(new Reference($backendServices[$primaryBackendConfigInfo->getId()]));

        $unicornDef->addArgument($unicornPrimaryBackendDef);

        // secondary

        $secondaryBackendArray = array();

        $secondaryBackendConfigInfos = $resolvedUnicornInfo->getSecondaryBackendConfigInfos();
        foreach ($secondaryBackendConfigInfos as $secondaryBackendConfigInfo) {
            $managedPartClasses = $this->getManagedPartClasses($secondaryBackendConfigInfo, $backendConfigs, $partMigrationInfos);

            $unicornSecondaryBackendDef = new Definition('Evispa\ResourceApiBundle\Unicorn\UnicornSecondaryBackend');
            $unicornSecondaryBackendDef->addArgument($secondaryBackendConfigInfo->getId());
            $unicornSecondaryBackendDef->addArgument($managedPartClasses);
            $unicornSecondaryBackendDef->addArgument(new Reference($backendServices[$secondaryBackendConfigInfo->getId()]));

            $secondaryBackendArray[] = $unicornSecondaryBackendDef;
        }

        $unicornDef->addArgument($secondaryBackendArray);
        
        return $unicornDef;
    }

    private function getResourcePartClasses($resourceClassName, $resourceParts, $annotationReader) {
        $class = new \ReflectionClass($resourceClassName);
        $partClasses = array();
        
        foreach ($resourceParts as $partName => $propertyName) {
            $property = $annotationReader->getPropertyAnnotation(
                $class->getProperty($propertyName),
                'JMS\Serializer\Annotation\Type'
            );

            if (null === $property) {
                throw new LogicException(
                    'Resource "' . $class->getName() .
                    '" property "' . $propertyName .
                    '" should have JMS\Serializer\Annotation\Type annotation.'
                );
            }

            $partClasses[$partName] = $property->name;
        }
        
        return $partClasses;
    }
    
    /**
     * 
     * @param array $partClasses
     * @param \Doctrine\Common\Annotations\Reader $versionReader
     * @param VersionReader $versionReader
     */
    private function getPartMigrationInfos($partClasses, $versionReader) {
        $partMigrationInfo = array();
        
        foreach ($partClasses as $partName => $className) {
            $migrationInfoBaker = new ClassMigrationBaker();
            $migrationInfo = $migrationInfoBaker->bakeMigrationInfo($versionReader, $className);
            
            $partMigrationInfo[$partName] = $migrationInfo;
        }
        
        return $partMigrationInfo;
    }
    
    public function process(ContainerBuilder $container)
    {
        //$container->getDefinition('evispa_resource_api.api_config_registry');
        $apiConfigs = $container->get('evispa_resource_api.api_config_registry');
        $backendServices = $this->getBackendServices($container);

        $annotationReader = new AnnotationReader();
        $versionReader = new VersionReader($annotationReader);
        
        foreach ($apiConfigs->getApiConfigs() as $apiConfig) {
            $resourceClassName = $apiConfig->getResourceClass()->getName();

            $resourceDef = new Definition('Evispa\ResourceApiBundle\Manager\ResourceManager');
            $resourceDef->addArgument($resourceClassName);

            $resourceDef->addArgument($apiConfig->getParts());            
            
            $migrationInfoBaker = new ClassMigrationBaker();
            $migrationInfo = $migrationInfoBaker->bakeMigrationInfo($versionReader, $resourceClassName);
            
            $resourceDef->addArgument($migrationInfoBaker->bakeServiceDefinition($migrationInfo));

            $unicornDriverId = 'resource_api.'.$apiConfig->getResourceId().'.unicorn';
            
            $backendConfigs = $container->get('evispa_resource_api.backend_config_registry');
            $resolvedUnicornInfo = $this->resolveUnicornInfo($container, $apiConfig, $backendConfigs);
            
            $partClasses = $this->getResourcePartClasses($resourceClassName, $apiConfig->getParts(), $annotationReader);
            $partMigrationInfos = $this->getPartMigrationInfos($partClasses, $versionReader);
            
            $unicornDef = $this->getUnicornDefinition($container, $resolvedUnicornInfo, $partMigrationInfos, $backendServices, $backendConfigs);
            $unicornDef->setLazy(true);
            $container->addDefinitions(array($unicornDriverId => $unicornDef));
            
            $requiredClassOptions = $versionReader->getRequiredClassOptions($resourceClassName);
            foreach ($partClasses as $partName => $partClassName) {
                $requiredPartOptions = $versionReader->getRequiredClassOptions($partClassName);
                foreach ($requiredPartOptions as $optionName => $info) {
                    $requiredClassOptions[$optionName] = $info;
                }
            }
            
            $resourceDef->addArgument($requiredClassOptions);
            
            $resourceDef->addArgument(new Reference($unicornDriverId));

            $container->addDefinitions(array('resource_api.'.$apiConfig->getResourceId() => $resourceDef));
        }
    }
}