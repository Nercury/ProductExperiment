<?php

namespace Evispa\ResourceApiBundle\Manager;

use Doctrine\Common\Annotations\Reader;
use Evispa\Api\Resource\Model\ApiResourceInterface;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Unicorn\Unicorn;
use Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author nerijus
 */
class OldResourceManager
{
    /**
     * Used to read/write resource properties.
     *
     * @var PropertyAccessor
     */
    private $propertyAccess;

    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * Resource property list from config, (property.id) => (property).
     *
     * @var array
     */
    private $resourceProperties;

    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $versionReader;

    /**
     * Version converter for each resource part.
     *
     * @var VersionConverter[]
     */
    private $partVersionConverter;

    /**
     * Unicorn - backend configuration set.
     *
     * @var Unicorn
     */
    private $unicorn;

    /**
     * Used converter options.
     *
     * @var array
     */
    private $converterOptions;

    /**
     * @param Reader $reader
     * @param VersionReader $versionReader
     * @param array $converterOptions
     * @param \ReflectionClass $class
     * @param array $resourceProperties
     * @param Unicorn $unicorn
     *
     * @throws LogicException
     */
    public function __construct(
        Reader $reader,
        VersionReader $versionReader,
        array $converterOptions,
        \ReflectionClass $class,
        array $resourceProperties,
        Unicorn $unicorn
    )
    {
        $this->versionReader = $versionReader;
        $this->propertyAccess = PropertyAccess::createPropertyAccessor();
        $this->class = $class;
        $this->resourceProperties = $resourceProperties;
        $this->unicorn = $unicorn;
        $this->converterOptions = $converterOptions;

        foreach ($this->resourceProperties as $partName => $propertyName) {
            $property = $reader->getPropertyAnnotation(
                $this->class->getProperty($propertyName),
                'JMS\Serializer\Annotation\Type'
            );

            if (null === $property) {
                throw new LogicException(
                    'Resource "' . $this->class->getName() .
                    '" property "' . $propertyName .
                    '" should have JMS\Serializer\Annotation\Type annotation.'
                );
            }

            $this->partVersionConverter[$partName] = new VersionConverter(
                $versionReader,
                $property->name,
                $converterOptions
            );
        }
    }

    /**
     * Get the name of managed class.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->class->getName();
    }

    /**
     * Get version reader used by this manager.
     *
     * @return VersionReader
     */
    public function getVersionReader()
    {
        return $this->versionReader;
    }

    /**
     * Get used converter options.
     *
     * @return array
     */
    public function getConverterOptions()
    {
        return $this->converterOptions;
    }

    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @throws \LogicException
     *
     * @return ApiResourceInterface|null
     */
    public function fetchOne($slug)
    {
        $resultObject = $this->unicorn->getPrimaryBackend()->fetchOne($slug);

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
                $this->resourceProperties[$partName],
                $this->partVersionConverter[$partName]->migrateFrom($part)
            );
        }

        // set parts form secondary backends
        foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
            $otherParts = $unicornBackend->fetchOne($slug);

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

        return $resource;
    }

    /**
     * Find batch of resources
     *
     * @param FindParameters $params
     *
     * @return FindResult
     */
    public function fetchAll(FindParameters $params)
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

    /**
     * Create and get a new resource object, no persist to the db.
     *
     * @return ApiResourceInterface
     */
    public function getNew()
    {

    }

    /**
     * Save a resource object to the database.
     *
     * @param ApiResourceInterface $resource
     */
    public function saveOne($resource)
    {

    }
}