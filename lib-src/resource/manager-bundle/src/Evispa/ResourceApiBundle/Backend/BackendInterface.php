<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
interface BackendInterface
{
    public function findOne($slug, array $requestedParts);
}