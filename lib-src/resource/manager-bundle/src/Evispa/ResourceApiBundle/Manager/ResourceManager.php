<?php

namespace Evispa\ResourceApiBundle\Manager;

/**
 * @author nerijus
 */
class ResourceManager
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @param \ReflectionClass $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @return \Evispa\Api\Resource\Model\ApiResourceInterface
     */
    public function findOne($slug) {

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