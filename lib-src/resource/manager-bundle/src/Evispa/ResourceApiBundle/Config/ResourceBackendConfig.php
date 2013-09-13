<?php

namespace Evispa\ResourceApiBundle\Config;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface;

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
     * @param PrimaryBackendInterface $primaryBackend
     * @param SecondaryBackendInterface $secondaryBackend
     * @return \self
     */
    public static function create(
        $backendId,
        $resourceId,
        $parts,
        PrimaryBackendInterface $primaryBackend = null,
        SecondaryBackendInterface $secondaryBackend = null
    )
    {
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
     * @return PrimaryBackendInterface
     */
    public function getPrimaryBackend()
    {
        return $this->primaryBackend;
    }

    /**
     * @return SecondaryBackendInterface
     */
    public function getSecondaryBackend()
    {
        return $this->secondaryBackend;
    }

    /**
     * Set primary backend manager.
     *
     * @param PrimaryBackendInterface $backendManager
     * @return ResourceBackendConfig
     */
    public function setPrimaryBackend(PrimaryBackendInterface $backendManager)
    {
        $this->primaryBackend = $backendManager;
        return $this;
    }

    /**
     * Set secondary backend manager.
     *
     * @param SecondaryBackendInterface $partManager
     * @return ResourceBackendConfig
     */
    public function setSecondaryBackend(SecondaryBackendInterface $partManager)
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