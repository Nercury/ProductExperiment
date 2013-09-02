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
     * @var string
     */
    private $resourceClass;

    /**
     *
     * @param string $resourceId Resource identifier, i.e. "product".
     * @param string $resourceClass Resource class name, i.e "Vendor\Product".
     */
    function __construct($resourceId, $resourceClass)
    {
        $this->resourceId = $resourceId;
        $this->resourceClass = $resourceClass;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function getResourceClass()
    {
        return $this->resourceClass;
    }
}