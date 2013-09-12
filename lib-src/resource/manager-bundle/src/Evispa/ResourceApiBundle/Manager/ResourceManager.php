<?php
/**
 * @author nerijus
 */

namespace Evispa\ResourceApiBundle\Manager;

use Evispa\ResourceApiBundle\Backend\FetchParameters;

class ResourceManager
{
    /**
     * Primary class of this resource manager.
     *
     * @var string
     */
    private $className;

    /**
     * List of available input versions.
     *
     * @var array
     */
    private $inputMigrationVersions;

    /**
     * Information about actions to execute migrations from specified versions.
     *
     * @var type
     */
    private $inputMigrationPaths;

    /**
     * Actual actions to migrate object from specified version.
     *
     * @var array
     */
    private $inputMigrationActions = array();

    /**
     * List of available output versions.
     *
     * @var array
     */
    private $outputMigrationVersions;

    /**
     * Information about actions to execute migrations to specified versions.
     *
     * @var array
     */
    private $outputMigrationPaths;

    /**
     * Actual actions to migrate object to specified version.
     *
     * @var array
     */
    private $outputMigrationActions = array();

    private $classVersions = array();
    
    /**
     * Backend driver unicorn.
     *
     * @var \Evispa\ResourceApiBundle\Unicorn\Unicorn
     */
    private $unicorn;

    public function __construct(
        $className,
        $inputMigrationVersions,
        $inputMigrationPaths,
        $outputMigrationVersions,
        $outputMigrationPaths,
        $classVersions,
        $unicorn
    ) {
        $this->className = $className;
        $this->inputMigrationVersions = $inputMigrationVersions;
        $this->inputMigrationPaths = $inputMigrationPaths;
        $this->outputMigrationVersions = $outputMigrationVersions;
        $this->outputMigrationPaths = $outputMigrationPaths;
        $this->classVersions = $classVersions;
        $this->unicorn = $unicorn;
    }

    public function getInputMigrationVersions()
    {
        return $this->inputMigrationVersions;
    }

    public function getOutputMigrationVersions()
    {
        return $this->outputMigrationVersions;
    }

    public function getInputMigrationActions($className) {
        if (isset($this->inputMigrationActions[$className])) {
            return $this->inputMigrationActions[$className];
        }

        $actions = array();

        if (!isset($this->inputMigrationPaths[$className])) {
            throw new \Evispa\ResourceApiBundle\Exception\ObjectMigrationException('Invalid input class '.$className.'.');
        }

        foreach ($this->inputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = \Evispa\ObjectMigration\Action\ActionSerializer::deserializeAction($itemData);
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
            throw new \Evispa\ResourceApiBundle\Exception\ObjectMigrationException('Invalid output class '.$className.'.');
        }

        foreach ($this->outputMigrationPaths[$className] as $pathItems) {
            foreach ($pathItems as $itemData) {
                $actions[] = \Evispa\ObjectMigration\Action\ActionSerializer::deserializeAction($itemData);
            }
        }

        $this->outputMigrationActions[$className] = $actions;
        return $actions;
    }
    
    public function getClassVersion($className) {
        if (!isset($this->classVersions[$className])) {
            throw new \LogicException('Class name "'.$className.'" is not known for "'.$className.'" resource manager.');
        }
        return $this->classVersions[$className];
    }
    
    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @throws \LogicException
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface|null
     */
    public function fetchOne($slug)
    {
        
    }
    
    /**
     * Find batch of resources
     *
     * @param FetchParameters $params
     *
     * @return FetchResult
     */
    public function fetchAll(FetchParameters $params)
    {        
        return new FetchResult($params, array(), 0);
    }
}
