<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
class PartWrapper
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