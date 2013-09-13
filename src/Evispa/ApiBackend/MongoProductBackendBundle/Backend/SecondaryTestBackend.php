<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;

/**
 * Description of SecondaryTestBackend
 *
 * @author nerijus
 */
class SecondaryTestBackend implements \Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface
{

    public function fetchBySlugs(array $slugs, array $requestedParts)
    {
        return array();
    }

    public function fetchOne($slug, array $requestedParts)
    {

    }
}