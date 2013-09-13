<?php

namespace Evispa\ResourceApiBundle\Unicorn\Config;

/**
 * @author nerijus
 */
class UnicornConfigInfo
{
    private $primaryBackendConfigInfo;
    private $secondaryBackendConfigInfos = array();

    /**
     * @param PrimaryBackendConfigInfo $primaryBackendConfigInfo
     */
    function __construct(PrimaryBackendConfigInfo $primaryBackendConfigInfo)
    {
        $this->primaryBackendConfigInfo = $primaryBackendConfigInfo;
    }

    /**
     *
     * @param SecondaryBackendConfigInfo $configInfo
     */
    public function addSecondaryBackendConfigInfo(SecondaryBackendConfigInfo $configInfo)
    {
        $this->secondaryBackendConfigInfos[] = $configInfo;
    }

    /**
     * @return PrimaryBackendConfigInfo
     */
    public function getPrimaryBackendConfigInfo()
    {
        return $this->primaryBackendConfigInfo;
    }

    /**
     * @return SecondaryBackendConfigInfo[]
     */
    public function getSecondaryBackendConfigInfos()
    {
        return $this->secondaryBackendConfigInfos;
    }
}