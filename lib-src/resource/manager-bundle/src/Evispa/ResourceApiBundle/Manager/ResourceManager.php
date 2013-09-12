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
     * Class migration information.
     *
     * @var \Evispa\ResourceApiBundle\Migration\ClassMigrationInfo
     */
    private $migrationInfo;
    
    /**
     * Backend driver unicorn.
     *
     * @var \Evispa\ResourceApiBundle\Unicorn\Unicorn
     */
    private $unicorn;

    public function __construct(
        $className,
        \Evispa\ResourceApiBundle\Migration\ClassMigrationInfo $migrationInfo,
        \Evispa\ResourceApiBundle\Unicorn\Unicorn $unicorn
    ) {
        $this->className = $className;
        $this->migrationInfo = $migrationInfo;
        $this->unicorn = $unicorn;
    }

    public function getInputMigrationVersions()
    {
        return $this->migrationInfo->inputMigrationVersions;
    }

    public function getOutputMigrationVersions()
    {
        return $this->migrationInfo->outputMigrationVersions;
    }
    
    public function getClassVersion($className) {
        if (!isset($this->migrationInfo->classVersions[$className])) {
            throw new \LogicException('Class name "'.$className.'" is not known for "'.$className.'" resource manager.');
        }
        return $this->migrationInfo->classVersions[$className];
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
        $resultsObject = $this->unicorn->getPrimaryBackend()->fetchAll($params);

        $resources = array();

        foreach ($resultsObject->getObjects() as $resultObject) {

            // Create a new resource.

            /** @var ApiResourceInterface $resource */
            $resource = $this->class->newInstance();
            $resource->setSlug($resultObject->getResourceSlug());

            foreach ($resultObject->getResourceParts() as $partName => $part) {

                if (null === $part) {
                    continue;
                }

                $this->propertyAccess->setValue(
                    $resource,
                    $this->resourceProperties[$partName],
                    $this->partVersionConverter[$partName]->migrateFrom($part)
                );
            }

            $resources[$resultObject->getResourceSlug()] = $resource;
        }

        $slugs = array_keys($resources);

        if (0 < count($slugs)) {
            // set parts form secondary backends
            foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
                $resourcesParts = $unicornBackend->fetchBySlugs($slugs);

                foreach ($resourcesParts as $slug => $otherParts) {

                    foreach ($otherParts as $partName => $part) {
                        if (null === $part) {
                            continue;
                        }

                        $this->propertyAccess->setValue(
                            $resource,
                            $this->resourceProperties[$partName],
                            $this->partVersionConverter[$partName]->migrateFrom($part)
                        );
                    }
                }
            }
        }

        return new FindResult($params, $resources, $resultsObject->getTotalFound());
    }
}
