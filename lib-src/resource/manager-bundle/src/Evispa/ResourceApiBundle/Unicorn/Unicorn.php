<?php

namespace Evispa\ResourceApiBundle\Unicorn;

/**
 * We named this class Unicorn because alternative was ResolvedBackendAndConfigurationSetWrapper.
 *
 * It contains all actually configured backends with the assigned parts.
 */
class Unicorn
{

    /**
     * @var UnicornPrimaryBackend
     * */
    private $primaryBackend = null;

    /**
     * @var UnicornSecondaryBackend[]
     */
    private $secondaryBackends = array();

    /**
     * @param UnicornPrimaryBackend $primaryBackend
     * @param array $backends
     */
    public function __construct(UnicornPrimaryBackend $primaryBackend, $backends = array())
    {
        $this->primaryBackend = $primaryBackend;
        $this->secondaryBackends = $backends;
    }

    /**
     * @return UnicornPrimaryBackend
     */
    public function getPrimaryBackend()
    {
        return $this->primaryBackend;
    }

    /**
     * @param UnicornSecondaryBackend $backend
     */
    public function addSecondaryBackend(UnicornSecondaryBackend $backend)
    {
        $this->secondaryBackends[] = $backend;
    }

    /**
     * @return UnicornSecondaryBackend[]
     */
    public function getSecondaryBackends()
    {
        return $this->secondaryBackends;
    }
}
