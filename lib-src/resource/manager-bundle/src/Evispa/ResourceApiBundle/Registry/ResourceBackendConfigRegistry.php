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
        
        foreach ($this->backendConfigs as $backendConfig) {
            if (null !== $backendConfig->getPrimaryBackend() && $backendConfig->getPrimaryBackend() === $resourceBackendConfig->getPrimaryBackend()) {
                throw new \Evispa\ResourceApiBundle\Exception\BackendConfigurationException(
                    'Can not share the same primary backend among "'.$backendConfig->getBackendId().'" and '.
                    '"'.$resourceBackendConfig->getBackendId().'" configurations. Please fix this hack attempt :)'
                );
            }
            if (null !== $backendConfig->getSecondaryBackend() && $backendConfig->getSecondaryBackend() === $resourceBackendConfig->getSecondaryBackend()) {
                throw new \Evispa\ResourceApiBundle\Exception\BackendConfigurationException(
                    'Can not share the same secondary backend among "'.$backendConfig->getBackendId().'" and '.
                    '"'.$resourceBackendConfig->getBackendId().'" configurations. Please fix this hack attempt :)'
                );
            }
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
    
    public function getBackendConfig($id) {
        return isset($this->backendConfigs[$id]) ? $this->backendConfigs[$id] : null;
    }
}