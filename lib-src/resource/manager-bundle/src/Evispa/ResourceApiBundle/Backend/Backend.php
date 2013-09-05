<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
class Backend
{
    private $backendManager;
    private $backendParts;

    private $partManagersWrappers;

    public function getBackendManager()
    {
        return $this->backendManager;
    }

    public function setBackendManager($backendManager)
    {
        $this->backendManager = $backendManager;
        return $this;
    }

    
}