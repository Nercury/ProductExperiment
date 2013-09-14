<?php
/**
 * @author nerijus
 */

namespace Evispa\ResourceApiBundle\Manager;

use Evispa\Api\Resource\Model\ApiResourceInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
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
                    'Required option "' . $optionName . '" was not set. It is required to migrate from "' . $info
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
}
