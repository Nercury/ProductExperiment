<?php

namespace Evispa\ResourceApiBundle\Config;

/**
 * @author nerijus
 */
class ResourceApiConfig
{
    /**
     * Resource identifier, i.e. "product".
     *
     * @var string
     */
    private $resourceId;

    /**
     * Resource class name, i.e "Vendor\Product".
     *
     * @var \ReflectionClass
     */
    private $resourceClass;

    private $parts;

    /**
     *
     * @param string $resourceId Resource identifier, i.e. "product".
     * @param string $resourceClass Resource class name, i.e "Vendor\Product".
     */
    function __construct($resourceId, $resourceClass, $parts)
    {
        $this->resourceId = $resourceId;
        $this->resourceClass = new \ReflectionClass($resourceClass);
        $this->parts = $parts;

        $requiredInterface = 'Evispa\Api\Resource\Model\ApiResourceInterface';

        if (!$this->resourceClass->implementsInterface($requiredInterface)) {
            throw new \LogicException('Root resource class "'.$resourceClass.'" must implement "'.$requiredInterface.'" interface.');
        }
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return \ReflectionClass
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * Return array of (part identifier) => (part property name).
     *
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }
}