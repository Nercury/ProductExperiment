<?php

namespace Evispa\ResourceApiBundle\Migration;

use Evispa\ObjectMigration\Action\ActionSerializer;
use Evispa\ObjectMigration\Action\MigrationActionInterface;

/**
 * @author Nerijus
 */
class ClassMigrationInfo
{

    public $inputVersions;
    public $inputMigrationPaths;
    public $outputVersions;
    public $outputMigrationPaths;
    public $classVersions;

    /**
     * Actual actions to migrate object from specified version.
     *
     * @var array
     */
    private $inputMigrationActions = array();

    /**
     * Actual actions to migrate object to specified version.
     *
     * @var array
     */
    private $outputMigrationActions = array();

    public function __construct(
        $inputVersions = array(),
        $inputMigrationPaths = array(),
        $outputVersions = array(),
        $outputMigrationPaths = array(),
        $classVersions = array()
    ) {
        $this->inputVersions = $inputVersions;
        $this->inputMigrationPaths = $inputMigrationPaths;
        $this->outputVersions = $outputVersions;
        $this->outputMigrationPaths = $outputMigrationPaths;
        $this->classVersions = $classVersions;
    }

    /**
     * @param $className
     *
     * @return MigrationActionInterface[]|null
     */
    public function getInputMigrationActions($className)
    {
        if (isset($this->inputMigrationActions[$className])) {
            return $this->inputMigrationActions[$className];
        }

        $actions = array();

        if (!isset($this->inputMigrationPaths[$className])) {
            return null;
        }

        foreach ($this->inputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = ActionSerializer::deserializeAction($itemData);
            }
        }

        $this->inputMigrationActions[$className] = $actions;
        return $actions;
    }

    /**
     * @param $className
     *
     * @return MigrationActionInterface[]|null
     */
    public function getOutputMigrationActions($className)
    {
        if (isset($this->outputMigrationActions[$className])) {
            return $this->outputMigrationActions[$className];
        }

        $actions = array();

        if (!isset($this->outputMigrationPaths[$className])) {
            return null;
        }

        foreach ($this->outputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = ActionSerializer::deserializeAction($itemData);
            }
        }

        $this->outputMigrationActions[$className] = $actions;
        return $actions;
    }

    public function getClassVersion($className)
    {
        if (!isset($this->classVersions[$className])) {
            throw new \Exception('Class name "' . $className . '" is not known for "' . $className . '" resource manager.');
        }
        return $this->classVersions[$className];
    }
}
