<?php

namespace Evispa\ResourceApiBundle\Manager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ResourceApiBundle\Backend\Unicorn;
use Symfony\Component\Form\Exception\LogicException;

/**
 * @author nerijus
 */
class ResourceManager
{
    private $propertyAccess;

    /**
     * @var \ReflectionClass
     */
    private $class;

    private $resourceProperties;

    /**
     * @var VersionConverter[]
     */
    private $partVersionConverter;

    /**
     * @var Unicorn
     */
    private $unicorn;

    /**
     * @param Reader        $reader
     * @param VersionReader $versionReader
     * @param array         $converterOptions
     * @param               $class
     * @param               $resourceProperties
     * @param Unicorn       $unicorn
     *
     * @throws \Symfony\Component\Form\Exception\LogicException
     */
    public function __construct(
        Reader $reader,
        VersionReader $versionReader,
        array $converterOptions,
        $class,
        $resourceProperties,
        Unicorn $unicorn
    ) {
        $this->propertyAccess = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
        $this->class = $class;
        $this->resourceProperties = $resourceProperties;
        $this->unicorn = $unicorn;

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
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @throws \LogicException
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface
     */
    public function findOne($slug)
    {
        $resource = $this->class->newInstance();
        $resource->setSlug($slug);

        foreach ($this->unicorn->getBackends() as $unicornBackend) {
            $realBackend = $unicornBackend->getBackend();
            $backendPartClasses = $unicornBackend->getManagedParts();
            $backendPartNames = array_keys($backendPartClasses);

            $backendResult = $realBackend->findOne($slug, $backendPartNames);
            foreach ($backendPartNames as $partName) {
                if (!isset($backendResult[$partName])) {
                    throw new \LogicException(
                        'Expected part "' . $partName . '" not found in backend "' . get_class($realBackend) . '".'
                    );
                }

                $part = $this->partVersionConverter[$partName]->migrateFrom($backendResult[$partName]);

                $this->propertyAccess->setValue($resource, $this->resourceProperties[$partName], $part);
            }
        }

        return $resource;
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