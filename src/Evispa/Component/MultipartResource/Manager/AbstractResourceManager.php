<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Manager;

use Evispa\Component\MultipartResource\Config\ResourceConfig;
use Evispa\Component\MultipartResource\Config\ResourcePartConfig;
use Evispa\Component\MultipartResource\Data\CombinedData;

/**
 * Implement this class to manage your resource.
 */
abstract class AbstractResourceManager
{
    /**
     * Resource configuration.
     *
     * @var ResourceConfig
     */
    private $resourceConfig;

    public function __construct(ResourceConfig $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    private function buildInternalData($objectId, CombinedData $data, ResourcePartConfig $config)
    {


        if (null === $crud) {
            $data[$config->getId()] = null;
            return;
        }

        $data[$config->getId()] = $crud->get($objectId);
    }

    /**
     * Get data for resource.
     *
     * @param string $id Resource identifier.
     *
     * @return mixed
     */
    public function get($id = null)
    {
        if (null === $id) {
            $object = $this->createNewObject();
        } else {
            $objects = $this->loadExistingObjects(array($id));
            if (0 === count($objects)) {
                return null;
            }
            $object = $objects[$id];
        }

        $data = new CombinedData();
        foreach ($this->resourceConfig->resourcePartConfigs as $config) {
            $this->buildInternalData($object, $data, $config);
        }

        foreach ($this->resourceConfig->resourcePartConfigs as $config) {
            $this->buildExternalData(array($id), $data, $config);
        }

        return $data;
    }

    /**
     * Create a new empty object.
     */
    abstract protected function createNewObject();

    /**
     * Load objects based on ids.
     */
    abstract protected function loadExistingObjects($ids);
}
