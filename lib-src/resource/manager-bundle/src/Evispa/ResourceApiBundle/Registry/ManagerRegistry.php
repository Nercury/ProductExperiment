<?php

namespace Evispa\ResourceApiBundle\Registry;

/**
 * @author nerijus
 */
class ManagerRegistry
{
    private $loadedManagers = array();

    /**
     * @var ApiConfigRegistry
     */
    protected $apiConfigRegistry;

    /**
     * @var ResourceBackendConfigRegistry
     */
    protected $backendConfigRegistry;

    /**
     * @var \Evispa\ResourceApiBundle\Backend\ApiBackendResolver
     */
    protected $backendResolver;

    protected $apiBackendMapConfiguration;

    /**
     * Set api config registry.
     *
     * @param ApiConfigRegistry $apiConfigRegistry
     */
    public function setApiConfigRegistry(ApiConfigRegistry $apiConfigRegistry) {
        $this->apiConfigRegistry = $apiConfigRegistry;
    }

    public function setBackendConfigRegistry(ResourceBackendConfigRegistry $backendConfigRegistry) {
        $this->backendConfigRegistry = $backendConfigRegistry;
    }

    public function setBackendResolver($backendResolver) {
        $this->backendResolver = $backendResolver;
    }

    public function setApiBackendMap($mapConfiguration) {
        $this->apiBackendMapConfiguration = $mapConfiguration;
    }

    protected function loadManagerForConfig(\Evispa\ResourceApiBundle\Config\ResourceApiConfig $config) {

        $unicorn = $this->backendResolver->createBackend($config, $this->backendConfigRegistry, $this->apiBackendMapConfiguration);

        $manager = new \Evispa\ResourceApiBundle\Manager\ResourceManager($config->getResourceClass(), $config->getParts(), $unicorn);

        return $manager;
    }

    /**
     * Get resource manager.
     *
     * @param string $resourceId
     *
     * @return \Evispa\ResourceApiBundle\Manager\ResourceManager
     */
    public function getResourceManager($resourceId) {
        if (isset($this->loadedManagers[$resourceId])) {
            return $this->loadedManagers[$resourceId];
        }

        $resourceConfig = $this->apiConfigRegistry->getResourceConfig($resourceId);
        if (null === $resourceConfig) {
            return null;
        }

        $this->loadedManagers[$resourceId] = $this->loadManagerForConfig($resourceConfig);

        return $this->loadedManagers[$resourceId];
    }
}