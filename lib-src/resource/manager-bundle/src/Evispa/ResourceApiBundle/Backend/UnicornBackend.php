<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
class UnicornBackend
{
    private $managedParts = array();
    private $backend;

    function __construct(array $managedParts, PrimaryBackendInterface $backend)
    {
        $this->managedParts = $managedParts;
        $this->backend = $backend;
    }

    /**
     * @return string[]
     */
    public function getManagedParts()
    {
        return $this->managedParts;
    }

    /**
     * @return PrimaryBackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }
}
