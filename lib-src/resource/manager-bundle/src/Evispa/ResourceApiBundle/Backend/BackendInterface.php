<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
interface BackendInterface
{
    public function findOne($slug, array $requestedParts);
    public function find(FindParameters $params, array $requestedParts);
}