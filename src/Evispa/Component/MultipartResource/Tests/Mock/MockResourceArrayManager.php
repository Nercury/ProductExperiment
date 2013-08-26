<?php

namespace Evispa\Component\MultipartResource\Tests\Mock;

/**
 * Description of MockResourceArrayManager
 *
 * @author nerijus
 */
class MockResourceArrayManager implements \Evispa\Component\MultipartResource\Manager\ResourceManagerInterface
{

    private $objects;

    public function setObjects($objects)
    {
        $this->objects = $objects;
        return $this;
    }

    public function create()
    {

    }

    public function getManager()
    {

    }
}
