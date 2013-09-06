<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * We named this class Unicorn because alternative was ResolvedBackendAndConfigurationSetWrapper.
 *
 * It contains all actually configured backends with the assigned parts.
 */
class Unicorn
{
    /**
     * @var UnicornBackend
     **/
    private $primaryBackend = null;

    /**
     * @var UnicornBackend[]
     */
    private $secondaryBackends = array();

    /**
     * @return UnicornBackend
     */
    public function getPrimaryBackend()
    {
        return $this->primaryBackend;
    }

    function __construct($primaryBackend, $backends = array())
    {
        $this->primaryBackend = $primaryBackend;
        $this->secondaryBackends = $backends;
    }

    public function addSecondaryBackend(UnicornBackend $backend) {
        $this->secondaryBackends[] = $backend;
    }

    /**
     * @return UnicornBackend[]
     */
    public function getSecondaryBackends() {
        return $this->secondaryBackends;
    }
}
