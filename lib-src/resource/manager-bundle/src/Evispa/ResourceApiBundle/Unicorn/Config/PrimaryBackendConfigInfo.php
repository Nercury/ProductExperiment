<?php

namespace Evispa\ResourceApiBundle\Unicorn\Config;

class PrimaryBackendConfigInfo
{
    private $id = null;
    private $managedParts = array();

    function __construct($id, $managedParts)
    {
        $this->id = $id;
        $this->managedParts = $managedParts;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getManagedParts()
    {
        return $this->managedParts;
    }
}
