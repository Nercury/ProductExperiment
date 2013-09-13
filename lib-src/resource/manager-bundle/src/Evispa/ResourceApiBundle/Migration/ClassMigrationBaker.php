<?php

namespace Evispa\ResourceApiBundle\Migration;
use Evispa\ObjectMigration\Action\ActionSerializer;
use Evispa\ObjectMigration\VersionPath\VersionPathSearch;
use Evispa\ObjectMigration\VersionReader;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Nerijus
 */
class ClassMigrationBaker
{

    /**
     * @param VersionReader $versionReader
     * @param $resourceClassName
     * @return ClassMigrationInfo
     */
    public function bakeMigrationInfo(VersionReader $versionReader, $resourceClassName)
    {

        $versionPathSearch = new VersionPathSearch($versionReader);

        $info = new ClassMigrationInfo();

        $info->inputVersions = $versionReader->getAllowedClassInputVersions($resourceClassName);

        foreach ($info->inputVersions as $inputVersion => $inputClass) {
            $migrationPath = array();

            if ($inputClass !== $resourceClassName) {
                $pathMethods = $versionPathSearch->find($inputClass, $resourceClassName);

                foreach ($pathMethods as $methodInfo) {
                    $serializedAction = ActionSerializer::serializeAction($methodInfo->action);
                    $migrationPath[] = $serializedAction;
                }
            }

            $info->inputMigrationPaths[$inputClass][] = $migrationPath;
        }

        $info->outputVersions = $versionReader->getAllowedClassOutputVersions($resourceClassName);

        foreach ($info->outputVersions as $outputVersion => $outputClass) {

            $migrationPath = array();

            if ($outputClass !== $resourceClassName) {
                $pathMethods = $versionPathSearch->find($resourceClassName, $outputClass);

                foreach ($pathMethods as $methodInfo) {
                    $serializedAction = ActionSerializer::serializeAction($methodInfo->action);
                    $migrationPath[] = $serializedAction;
                }
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
     * @return Definition
     */
    public function bakeServiceDefinition(ClassMigrationInfo $migrationInfo)
    {
        $def = new Definition('Evispa\ResourceApiBundle\Migration\ClassMigrationInfo');

        $def->addArgument($migrationInfo->inputVersions);
        $def->addArgument($migrationInfo->inputMigrationPaths);
        $def->addArgument($migrationInfo->outputVersions);
        $def->addArgument($migrationInfo->outputMigrationPaths);
        $def->addArgument($migrationInfo->classVersions);

        return $def;
    }
}