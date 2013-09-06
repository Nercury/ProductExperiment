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
     * @var UnicornBackend[]
     */
    private $backends;

    public function addBackend(UnicornBackend $backend) {
        $this->backends[] = $backend;
    }

    /**
     * @return UnicornBackend[]
     */
    public function getBackends() {
        return $this->backends;
    }
}
