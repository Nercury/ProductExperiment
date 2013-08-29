<?php
/**
 * @author nerijus
 */

namespace Evispa\ObjectMigration;

class MigrationMetadata
{
    /**
     * Version.
     *
     * @var string
     */
    public $version;

    /**
     * Available migrations to other versions.
     *
     * @var array<string, string> Array of version => methodName.
     */
    public $migrationsFrom;

    /**
     * Available migrations from other versions.
     *
     * @var array<string, string> Array of version => methodName.
     */
    public $migrationsTo;

    public function __construct($version, array $migrationsFrom = array(), array $migrationsTo = array())
    {
        $this->version = $version;
        $this->migrationsFrom = $migrationsFrom;
        $this->migrationsTo = $migrationsTo;
    }
}