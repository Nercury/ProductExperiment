<?php

namespace Evispa\ResourceApiBundle\Registry;

use Evispa\ResourceApiBundle\Config\ResourceApiConfig;

/**
 * @author nerijus
 */
class ApiConfigRegistry
{

    protected $apiConfigs = array();

    public function registerApiConfig(ResourceApiConfig $resourceApiConfig)
    {
        $resourceId = $resourceApiConfig->getResourceId();

        if (isset($this->apiConfigs[$resourceId])) {
            throw new \LogicException('Resource "' . $resourceId . '" can not be registered for "' . $resourceApiConfig->getResourceClass() . '", because it is already registered for "' . $this->apiConfigs[$resourceApiConfig->getResourceId()]->getResourceClass() . '".');
        }

        $this->apiConfigs[$resourceId] = $resourceApiConfig;
    }

    /**
     * @return ResourceApiConfig[]
     */
    public function getApiConfigs()
    {
        return $this->apiConfigs;
    }

    /**
     * Get resource api configuration.
     *
     * @param string $resourceId Resource identifier.
     *
     * @return ResourceApiConfig
     */
    public function getResourceConfig($resourceId)
    {
        if (isset($this->apiConfigs[$resourceId])) {
            return $this->apiConfigs[$resourceId];
        }

        return null;
    }
}
