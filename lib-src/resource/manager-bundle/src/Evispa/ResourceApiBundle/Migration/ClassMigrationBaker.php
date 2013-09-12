<?php

namespace Evispa\ResourceApiBundle\Migration;

/**
 * @author Nerijus
 */
class ClassMigrationBaker {
    
    /**
     * @return ClassMigrationInfo
     */
    public function bakeMigrationInfo(\Evispa\ObjectMigration\VersionReader $versionReader, $resourceClassName) {
        
        $versionPathSearch = new \Evispa\ObjectMigration\VersionPath\VersionPathSearch($versionReader);
        
        $info = new ClassMigrationInfo();
        
        $info->inputVersions = $versionReader->getAllowedClassInputVersions($resourceClassName);

        foreach ($info->inputVersions as $inputVersion => $inputClass) {
            $pathMethods = $versionPathSearch->find($inputClass, $resourceClassName);
            $migrationPath = array();

            foreach ($pathMethods as $methodInfo) {
                $serializedAction = \Evispa\ObjectMigration\Action\ActionSerializer::serializeAction($methodInfo->action);
                $migrationPath[] = $serializedAction;
            }

            $info->inputMigrationPaths[$inputClass][] = $migrationPath;
        }

        $info->outputVersions = $versionReader->getAllowedClassOutputVersions($resourceClassName);

        foreach ($info->outputVersions as $outputVersion => $outputClass) {
            $pathMethods = $versionPathSearch->find($resourceClassName, $outputClass);
            $migrationPath = array();

            foreach ($pathMethods as $methodInfo) {
                $serializedAction = \Evispa\ObjectMigration\Action\ActionSerializer::serializeAction($methodInfo->action);
                $migrationPath[] = $serializedAction;
            }

            $info->outputMigrationPaths[$outputClass][] = $migrationPath;
        }

        foreach ($info->inputVersions as $version => $className) {
            $info->classVersions[$className] = $version;
        }
        
        foreach ($info->outputVersions as $version => $className) {
            $info->classVersions[$className] = $version;
        }
        
        return $info;
    }
    
    /**
     * Create service definition which will create the same migration info in the container.
     * 
     * @param ClassMigrationInfo $migrationInfo Migration info.
     * 
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function bakeServiceDefinition(ClassMigrationInfo $migrationInfo) {
        $def = new \Symfony\Component\DependencyInjection\Definition('Evispa\ResourceApiBundle\Migration\ClassMigrationInfo');
        
        $def->addArgument($migrationInfo->inputVersions);
        $def->addArgument($migrationInfo->inputMigrationPaths);
        $def->addArgument($migrationInfo->outputVersions);
        $def->addArgument($migrationInfo->outputMigrationPaths);
        $def->addArgument($migrationInfo->classVersions);
        
        return $def;
    }
}