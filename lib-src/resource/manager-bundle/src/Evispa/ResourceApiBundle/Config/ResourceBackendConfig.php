<?php

namespace Evispa\ResourceApiBundle\Config;

/**
 * @author nerijus
 */
class ResourceBackendConfig
{
    private $backendId;
    private $resourceId;
    private $parts;
    private $backendManager = null;
    private $partManager = null;
    private $eventListener = null;

    function __construct($backendId, $resourceId, $parts)
    {
        $this->backendId = $backendId;
        $this->resourceId = $resourceId;
        $this->parts = $parts;
    }

    /**
     * @return \Evispa\ResourceApiBundle\Backend\BackendManagerInterface
     */
    public function getBackendManager()
    {
        return $this->backendManager;
    }

    public function getPartManager()
    {
        return $this->partManager;
    }

    public function getEventListener()
    {
        return $this->eventListener;
    }

    public function setBackendManager($backendManager)
    {
        $this->backendManager = $backendManager;
        return $this;
    }

    public function setPartManager($partManager)
    {
        $this->partManager = $partManager;
        return $this;
    }

    public function setEventListener($eventListener)
    {
        $this->eventListener = $eventListener;
        return $this;
    }

    public function getBackendId()
    {
        return $this->backendId;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function getParts()
    {
        return $this->parts;
    }
}