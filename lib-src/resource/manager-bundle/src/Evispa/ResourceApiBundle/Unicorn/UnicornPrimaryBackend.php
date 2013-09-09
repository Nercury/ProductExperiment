<?php

namespace Evispa\ResourceApiBundle\Unicorn;

use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;

/**
 * @author nerijus
 */
class UnicornPrimaryBackend
{
    /**
     * @var array
     */
    private $managedParts = array();

    /**
     * @var PrimaryBackendInterface
     */
    private $backend = null;

    /**
     * @param array                   $managedParts
     * @param PrimaryBackendInterface $backend
     */
    public function __construct(array $managedParts, PrimaryBackendInterface $backend)
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
