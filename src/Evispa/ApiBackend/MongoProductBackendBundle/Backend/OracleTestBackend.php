<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;

use Evispa\ApiBackend\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultsObject;

/**
 * Description of OracleTestBackend
 *
 * @author Nerijus
 */
class OracleTestBackend implements PrimaryBackendInterface
{

    public function fetchAll(FetchParameters $params, array $requestedParts)
    {
        return array();
    }

    public function fetchOne($slug, array $requestedParts)
    {

    }
}
