<?php
/**
 * @author nerijus
 */

namespace Evispa\ResourceApiBundle\Manager;

use Evispa\Api\Resource\Model\ApiResourceInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendObject;
use Evispa\ResourceApiBundle\Exception\ResourceRequestException;
use Evispa\ResourceApiBundle\Migration\ClassMigrationInfo;
use Evispa\ResourceApiBundle\Unicorn\Unicorn;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ResourceManager
{
    /**
     * Primary class of this resource manager.
     *
     * @var \ReflectionClass
     */
    private $class;

    /**
     * Resource parts.
     *
     * @var array
     */
    private $resourceParts;

    /**
     * Class migration information.
     *
     * @var ClassMigrationInfo
     */
    public $migrationInfo;

    /**
     * Required options for resource operations.
     *
     * @var array
     */
    private $requiredOptions;

    /**
     * Backend driver unicorn.
     *
     * @var Unicorn
     */
    private $unicorn;

    /**
     * Used to read/write resource properties.
     *
     * @var PropertyAccessor
     */
    private $propertyAccess;

    public function __construct(
        $className,
        $resourceParts,
        ClassMigrationInfo $migrationInfo,
        $requiredOptions,
        Unicorn $unicorn
    ) {
        $this->class = new \ReflectionClass($className);
        $this->resourceParts = $resourceParts;
        $this->migrationInfo = $migrationInfo;
        $this->requiredOptions = $requiredOptions;
        $this->unicorn = $unicorn;

        $this->propertyAccess = PropertyAccess::createPropertyAccessor();
    }


    private function checkOptions($options)
    {
        foreach ($this->requiredOptions as $optionName => $info) {

            if (!isset($options[$optionName])) {
                throw new ResourceRequestException(
                    'Required option "' . $optionName . '" was not set. It is required to migrate from "' .
                    $info['from']
                );
            }
        }
    }

    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     * @param array  $options
     *
     * @return ApiResourceInterface|null
     */
    public function fetchOne($slug, $options = array())
    {
        $this->checkOptions($options);

        $resultObject = $this->unicorn->getPrimaryBackend()->fetchOne($slug, $options);

        if (null === $resultObject) {
            return null;
        }

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
                $this->resourceParts[$partName],
                $part
            );
        }

        // set parts form secondary backends
        foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
            $otherParts = $unicornBackend->fetchOne($slug, $options);

            if (null !== $otherParts) {

                foreach ($otherParts as $partName => $part) {
                    if (null === $part) {
                        continue;
                    }

                    $this->propertyAccess->setValue(
                        $resource,
                        $this->resourceParts[$partName],
                        $part
                    );
                }

            }
        }

        return $resource;
    }

    /**
     * Find batch of resources
     *
     * @param FetchParameters $params
     * @param array           $options
     *
     * @return FetchResult
     */
    public function fetchAll(FetchParameters $params, $options = array())
    {
        $this->checkOptions($options);

        $resultsObject = $this->unicorn->getPrimaryBackend()->fetchAll($params, $options);

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
                    $this->resourceParts[$partName],
                    $part
                );
            }

            $resources[$resultObject->getResourceSlug()] = $resource;
        }

        $slugs = array_keys($resources);

        if (0 < count($slugs)) {
            // set parts form secondary backends
            foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
                $resourcesParts = $unicornBackend->fetchBySlugs($slugs, $options);

                foreach ($resourcesParts as $slug => $otherParts) {

                    if (isset($resources[$slug])) {

                        $resource = $resources[$slug];

                        foreach ($otherParts as $partName => $part) {
                            if (null === $part) {
                                continue;
                            }

                            $this->propertyAccess->setValue(
                                $resource,
                                $this->resourceParts[$partName],
                                $part
                            );
                        }
                    }
                }
            }
        }

        return new FetchResult($params, $resources, $resultsObject->getTotalFound());
    }

    public function getNew($options = array())
    {
        $this->checkOptions($options);

        /** @var ApiResourceInterface $resource */
        $resource = $this->class->newInstance();

        $resultObject = $this->unicorn->getPrimaryBackend()->getNew($options);
        foreach ($resultObject->getResourceParts() as $partName => $part) {

            if (null === $part) {
                continue;
            }

            $this->propertyAccess->setValue(
                $resource,
                $this->resourceParts[$partName],
                $part
            );
        }

        foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
            $otherParts = $unicornBackend->getNew($options);

            foreach ($otherParts as $partName => $part) {
                if (null === $part) {
                    continue;
                }

                $this->propertyAccess->setValue(
                    $resource,
                    $this->resourceParts[$partName],
                    $part
                );
            }
        }

        return $resource;
    }

    /**
     * @param ApiResourceInterface[] $resources
     * @param array                  $options
     */
    public function saveAll(array $resources, array $options = array())
    {
        $this->checkOptions($options);

        // convert resources to backend objects
        $backendObjects = array();

        foreach ($resources as $resource) {

            if (get_class($resource) !== $this->class->name) {
                throw new \LogicException(
                    'Manager can only manage ' . $this->class->name . ' resources. Got: ' . get_class($resource)
                );
            }

            $backendObject = new PrimaryBackendObject($resource->getSlug());

            foreach ($this->unicorn->getPrimaryBackend()->getManagedParts() as $partName => $null) {
                $part = $this->propertyAccess->getValue($resource, $this->resourceParts[$partName]);
                $backendObject->setPart($partName, $part);
            }

            $backendObjects[] = $backendObject;
        }

        $backendObjects = $this->unicorn->getPrimaryBackend()->saveAll($backendObjects, $options);

        // ensure numeric keys
        $resources = array_values($resources);

        if (count($resources) !== count($backendObjects)) {
            throw new \LogicException('Failed to save some objects...');
        }

        foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
            $i = 0;
            foreach ($backendObjects as $backendObject) {
                $resource = $resources[$i];

                foreach ($unicornBackend->getManagedParts() as $partName => $null) {
                    $part = $this->propertyAccess->getValue($resource, $this->resourceParts[$partName]);
                    $backendObject->setPart($partName, $part);
                }
                $i++;
            }
            // TODO: implement secondary backend save all
        }

        // convert backend objects back to resources
        $resources = array();

        foreach ($backendObjects as $backendObject) {

            /** @var ApiResourceInterface $resource */
            $resource = $this->class->newInstance();
            $resource->setSlug($backendObject->getResourceSlug());

            foreach ($backendObject->getResourceParts() as $partName => $part) {
                if (null === $part) {
                    continue;
                }

                $this->propertyAccess->setValue(
                    $resource,
                    $this->resourceParts[$partName],
                    $part
                );
            }

            $resources[$resource->getSlug()] = $resource;
        }

        return $resources;
    }
}
