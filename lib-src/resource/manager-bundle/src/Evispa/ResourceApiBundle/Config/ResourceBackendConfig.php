<?php

namespace Evispa\ResourceApiBundle\Config;

/**
 * @author nerijus
 */
class ResourceBackendConfig
{
    private $backendId;
    private $resourceId;
    private $parts = array();

    /**
     *
     * @var type
     */
    private $primaryBackend = null;
    private $secondaryBackend = null;

    /**
     * Create a new backend configuration with assigned backends.
     *
     * @param string $backendId Backend identifier.
     * @param string $resourceId Managed resource.
     * @param string[] $parts
     * @param \Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface $primaryBackend
     * @param \Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface $secondaryBackend
     * @return \self
     */
    public static function create(
        $backendId,
        $resourceId,
        $parts,
        \Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface $primaryBackend = null,
        \Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface $secondaryBackend = null
    ) {
        $new = new self($backendId, $resourceId, $parts);
        $new->primaryBackend = $primaryBackend;
        $new->secondaryBackend = $secondaryBackend;
        return $new;
    }

    /**
     * Create a new backend.
     *
     * @param string $backendId Backend identifier.
     * @param string $resourceId Managed resource.
     * @param string[] $parts
     */
    function __construct($backendId, $resourceId, $parts)
    {
        $this->backendId = $backendId;
        $this->resourceId = $resourceId;
        $this->parts = $parts;
    }

    /**
     * @return \Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface
     */
    public function getPrimaryBackend()
    {
        return $this->primaryBackend;
    }

    /**
     * @return \Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface
     */
    public function getSecondaryBackend()
    {
        return $this->secondaryBackend;
    }

    /**
     * Set primary backend manager.
     *
     * @param \Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface $backendManager
     * @return \Evispa\ResourceApiBundle\Config\ResourceBackendConfig
     */
    public function setPrimaryBackend(\Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface $backendManager)
    {
        $this->primaryBackend = $backendManager;
        return $this;
    }

    /**
     * Set secondary backend manager.
     *
     * @param \Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface $partManager
     * @return \Evispa\ResourceApiBundle\Config\ResourceBackendConfig
     */
    public function setSecondaryBackend(\Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface $partManager)
    {
        $this->secondaryBackend = $partManager;
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