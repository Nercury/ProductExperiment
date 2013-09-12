<?php

namespace Evispa\ResourceApiBundle\Migration;

use Evispa\ObjectMigration\Action\ActionSerializer;
use Evispa\ResourceApiBundle\Exception\ObjectMigrationException;

/**
 * @author Nerijus
 */
class ClassMigrationInfo {
    
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
    
    function __construct(
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
    
    public function getInputMigrationActions($className) {
        if (isset($this->inputMigrationActions[$className])) {
            return $this->inputMigrationActions[$className];
        }

        $actions = array();

        if (!isset($this->inputMigrationPaths[$className])) {
            throw new ObjectMigrationException('Invalid input class '.$className.'.');
        }

        foreach ($this->inputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = ActionSerializer::deserializeAction($itemData);
            }
        }

        $this->inputMigrationActions[$className] = $actions;
        return $actions;
    }

    public function getOutputMigrationActions($className) {
        if (isset($this->outputMigrationActions[$className])) {
            return $this->outputMigrationActions[$className];
        }

        $actions = array();

        if (!isset($this->outputMigrationPaths[$className])) {
            throw new ObjectMigrationException('Invalid output class '.$className.'.');
        }

        foreach ($this->outputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = ActionSerializer::deserializeAction($itemData);
            }
        }

        $this->outputMigrationActions[$className] = $actions;
        return $actions;
    }
    
}