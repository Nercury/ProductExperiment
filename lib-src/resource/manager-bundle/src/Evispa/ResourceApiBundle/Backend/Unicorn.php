<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
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
