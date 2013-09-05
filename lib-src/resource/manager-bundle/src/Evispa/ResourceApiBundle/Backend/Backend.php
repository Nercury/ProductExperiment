<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
class Backend
{
    /**
     * @var ManagerWrapper
     */
    private $backendManager;
    private $backendParts;

    private $partManagersWrappers;

    /**
     * @return ManagerWrapper
     */
    public function getBackendManager()
    {
        return $this->backendManager;
    }

    /**
     * @param ManagerWrapper $backendManager
     * @return self
     */
    public function setBackendManager(ManagerWrapper $backendManager)
    {
        $this->backendManager = $backendManager;
        return $this;
    }
}
