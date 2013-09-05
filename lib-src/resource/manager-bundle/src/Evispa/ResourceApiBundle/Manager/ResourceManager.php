<?php

namespace Evispa\ResourceApiBundle\Manager;

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
     * @var \Evispa\ResourceApiBundle\Backend\Unicorn
     */
    private $unicorn;

    /**
     * @param \ReflectionClass $class
     */
    public function __construct($class, $resourceProperties, \Evispa\ResourceApiBundle\Backend\Unicorn $unicorn)
    {
        $this->propertyAccess = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();

        $this->class = $class;
        $this->resourceProperties = $resourceProperties;
        $this->unicorn = $unicorn;
    }

    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface
     */
    public function findOne($slug) {
        $resource = $this->class->newInstance();
        $resource->setSlug($slug);

        foreach ($this->unicorn->getBackends() as $unicornBackend) {
            $realBackend = $unicornBackend->getBackend();
            $backendPartClasses = $unicornBackend->getManagedParts();
            $backendPartNames = array_keys($backendPartClasses);

            $backendResult = $realBackend->findOne($slug, $backendPartNames);
            foreach ($backendPartNames as $partName) {
                if (!isset($backendResult[$partName])) {
                    throw new \LogicException('Expected part "'.$partName.'" not found in backend "'.get_class($realBackend).'".');
                }

                $partObj = $backendResult[$partName];
                // migrate part obj to version in product

                $this->propertyAccess->setValue($resource, $this->resourceProperties[$partName], $partObj);
            }
        }

        return $resource;
    }

    /**
     * Create and get a new resource object, no persiting to the db.
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface
     */
    public function getNew() {

    }

    /**
     * Save a resource object to the database.
     *
     * @param \Evispa\Api\Resource\Model\ApiResourceInterface $resource
     */
    public function saveOne($resource) {

    }
}