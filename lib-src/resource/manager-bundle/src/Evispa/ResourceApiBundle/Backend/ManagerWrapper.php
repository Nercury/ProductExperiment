<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
class ManagerWrapper
{
    private $managedParts = array();

    public function getManagedParts()
    {
        return $this->managedParts;
    }

    public function setManagedParts($managedParts)
    {
        $this->managedParts = $managedParts;
        return $this;
    }
}