<?php

namespace Evispa\ObjectMigration;

/**
 * @author nerijus
 */
class VersionConverter
{
    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $reader;

    public function __construct(VersionReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get object version.
     *
     * Object should be marked with the annoation:
     *
     * <code>Evispa\ObjectMigration\Annotations\Resource("type", version="X")</code>.
     *
     * @param mixed $object Object instance.
     *
     * @return string
     */
    public function getVersion($object) {
        $className = get_class($object);
        return $this->reader->getClassVersionAnnotation($className)->version;
    }

    /**
     * Migrate object to specified version.
     *
     * @param mixed $object Object instance.
     * @param string $version Version name.
     *
     * @return mixed Migrated object.
     */
    public function migrate($object, $version) {
        $className = get_class($object);
        $migrationData = $this->reader->getMigrationMetadata($className);
        if (isset($migrationData->migrationsTo[$version])) {
            $methodInfo = $migrationData->migrationsTo[$version];
            /** @var \ReflectionMethod $method */
            $method = $methodInfo['method'];
            return $method->invoke($object);
        }
        return null;
    }

    /**
     * Check if object can be migrated to specified version.
     *
     * @param string $version
     */
    public function canMigrateToVersion($version) {

    }

    /**
     * Check if object can be migrated from specified version.
     *
     * @param string $version
     */
    public function canMigrateFromVersion($version) {

    }
}