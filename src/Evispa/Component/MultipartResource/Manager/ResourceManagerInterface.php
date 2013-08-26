<?php

namespace Evispa\Component\MultipartResource\Manager;

interface ResourceManagerInterface
{
    /**
     * Create a new instance of the resource.
     *
     * @return mixed
     */
    public function create();

    public function find($id);
}
