<?php

namespace Evispa\ResourceApiBundle\Manager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Evispa\Api\Resource\Model\ApiResourceInterface;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;
use Evispa\ResourceApiBundle\Unicorn\Unicorn;
use Evispa\ResourceApiBundle\Unicorn\UnicornPrimaryBackend;
use Symfony\Component\Form\Exception\LogicException;

/**
 * @author nerijus
 */
class ResourceManager
{
    /**
     * Used to read/write resource properties.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
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
     * @param Reader           $reader
     * @param VersionReader    $versionReader
     * @param array            $converterOptions
     * @param \ReflectionClass $class
     * @param array            $resourceProperties
     * @param Unicorn          $unicorn
     *
     * @throws \Symfony\Component\Form\Exception\LogicException
     */
    public function __construct(
        Reader $reader,
        VersionReader $versionReader,
        array $converterOptions,
        \ReflectionClass $class,
        array $resourceProperties,
        Unicorn $unicorn
    ) {
        $this->versionReader = $versionReader;
        $this->propertyAccess = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
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
     * @param FindParameters        $params
     * @param UnicornPrimaryBackend $unicornBackend
     *
     * @return \Evispa\ResourceApiBundle\Backend\PrimaryBackendResultsObject
     */
    private function getPrimaryBackendResults(FindParameters $params, $unicornBackend)
    {
        $realBackend = $unicornBackend->getBackend();
        $backendPartNames = $this->getBackendPartNames($unicornBackend);

        return $realBackend->find($params, $backendPartNames);
    }

    /**
     * @param string                $slug
     * @param UnicornPrimaryBackend $unicornBackend
     *
     * @return array
     */
    private function getResourceSecondaryBackendParts($slug, $unicornBackend)
    {
        $realBackend = $unicornBackend->getBackend();
        $backendPartNames = $this->getBackendPartNames($unicornBackend);

        return $realBackend->findOne($slug, $backendPartNames);
    }

    /**
     * @param array                 $slugs
     * @param UnicornPrimaryBackend $unicornBackend
     *
     * @return array
     */
    private function getResourcesSecondaryBackendParts(array $slugs, $unicornBackend)
    {
        $realBackend = $unicornBackend->getBackend();
        $backendPartNames = $this->getBackendPartNames($unicornBackend);

        return $realBackend->find($slugs, $backendPartNames);
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
    public function findOne($slug)
    {
        $resultObject = $this->unicorn->getPrimaryBackend()->fetchOne($slug);

        if (null === $resultObject) {
            return null;
        }

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

            $otherParts = $this->getResourceSecondaryBackendParts($slug, $unicornBackend);
            if (null === $otherParts) {
                continue;
            }
            $this->updateResourceForParts($unicornBackend, $otherParts, $resource);
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
    public function find(FindParameters $params)
    {
        $resultsObject = $this->getPrimaryBackendResults($params, $this->unicorn->getPrimaryBackend());

        $resources = array();

        foreach ($resultsObject->getObjects() as $resultObject) {
            // create new resource
            $resource = $this->createResource($resultObject);

            // set parts from primary backend
            $this->updateResourceForParts(
                $this->unicorn->getPrimaryBackend(),
                $resultObject->getResourceParts(),
                $resource
            );

            $resources[$resultObject->getResourceSlug()] = $resource;
        }

        $slugs = array_keys($resources);

        if (0 < count($slugs)) {
            // set parts form secondary backends
            foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
                $resourcesParts = $this->getResourcesSecondaryBackendParts($slugs, $unicornBackend);

                foreach ($resourcesParts as $slug => $resourceParts) {
                    $this->updateResourceForParts(
                        $this->unicorn->getPrimaryBackend(),
                        $resourceParts,
                        $resources[$slug]
                    );
                }
            }
        }

        return new FindResult($params, $resources, $resultsObject->getTotalFound());
    }

    /**
     * Create and get a new resource object, no persist to the db.
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface
     */
    public function getNew()
    {

    }

    /**
     * Save a resource object to the database.
     *
     * @param \Evispa\Api\Resource\Model\ApiResourceInterface $resource
     */
    public function saveOne($resource)
    {

    }
}