<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
interface BackendManagerInterface
{
    public function findOne($slug, array $requestedParts);
}