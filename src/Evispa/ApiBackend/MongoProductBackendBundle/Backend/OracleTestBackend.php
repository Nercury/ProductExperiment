<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;

use Evispa\ApiBackend\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendObject;
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

    public function getNew(array $requestedParts)
    {

    }

    /**
     * Save primary backend object
     *
     * @param PrimaryBackendObject $object
     * @param array                $saveParts
     *
     * @return mixed
     */
    public function save(PrimaryBackendObject $object, array $saveParts)
    {
        // TODO: Implement save() method.
    }

    /**
     * @param PrimaryBackendObject[] $objects
     * @param array                  $saveParts
     *
     * @return mixed
     */
    public function saveAll(array $objects, array $saveParts)
    {
        // TODO: Implement saveAll() method.
    }
}
