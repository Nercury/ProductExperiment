<?php

namespace Evispa\ResourceApiBundle\Registry;

use Evispa\ResourceApiBundle\Config\ResourceBackendConfig;
use LogicException;

/**
 * @author nerijus
 */
class ResourceBackendConfigRegistry
{
    protected $backendConfigs = array();

    public function registerBackendConfig(ResourceBackendConfig $resourceBackendConfig) {
        $backendId = $resourceBackendConfig->getBackendId();

        if (isset($this->backendConfigs[$backendId])) {
            throw new LogicException('Backend with id "'.$backendId.'" is already defined.');
        }

        $this->backendConfigs[$backendId] = $resourceBackendConfig;
    }

    /**
     * @return ResourceBackendConfig[]
     */
    public function getBackendConfigs()
    {
        return $this->backendConfigs;
    }
}