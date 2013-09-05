<?php

namespace Evispa\ResourceApiBundle\Registry;

use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\VersionReader;

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
     * @var \Evispa\ResourceApiBundle\Backend\ApiUnicornResolver
     */
    protected $unicornResolver;

    protected $apiBackendMapConfiguration;

    /**
     * Set api config registry.
     *
     * @param ApiConfigRegistry $apiConfigRegistry
     */
    public function setApiConfigRegistry(ApiConfigRegistry $apiConfigRegistry)
    {
        $this->apiConfigRegistry = $apiConfigRegistry;
    }

    public function setBackendConfigRegistry(ResourceBackendConfigRegistry $backendConfigRegistry)
    {
        $this->backendConfigRegistry = $backendConfigRegistry;
    }

    public function setUnicornResolver($unicornResolver)
    {
        $this->unicornResolver = $unicornResolver;
    }

    public function setApiBackendMap($mapConfiguration)
    {
        $this->apiBackendMapConfiguration = $mapConfiguration;
    }

    protected function loadManagerForConfig(\Evispa\ResourceApiBundle\Config\ResourceApiConfig $config, array $options)
    {
        $reader = new AnnotationReader();
        $versionReader = new VersionReader($reader);

        $unicorn = $this->unicornResolver->makeUnicorn(
            $config,
            $this->backendConfigRegistry,
            $this->apiBackendMapConfiguration
        );

        $manager = new \Evispa\ResourceApiBundle\Manager\ResourceManager(
            $reader, $versionReader, $options, $config->getResourceClass(), $config->getParts(), $unicorn
        );

        return $manager;
    }

    /**
     * Get resource manager.
     *
     * @param string $resourceId
     * @param array  $options
     *
     * @return \Evispa\ResourceApiBundle\Manager\ResourceManager
     */
    public function getResourceManager($resourceId, array $options = array())
    {
        $hash = md5(json_encode(array($resourceId, $options)));

        if (isset($this->loadedManagers[$hash])) {
            return $this->loadedManagers[$hash];
        }

        $resourceConfig = $this->apiConfigRegistry->getResourceConfig($resourceId);
        if (null === $resourceConfig) {
            return null;
        }

        $this->loadedManagers[$hash] = $this->loadManagerForConfig($resourceConfig, $options);

        return $this->loadedManagers[$hash];
    }
}