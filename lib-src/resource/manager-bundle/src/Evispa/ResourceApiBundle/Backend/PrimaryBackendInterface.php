<?php

namespace Evispa\ResourceApiBundle\Backend;

/**
 * @author nerijus
 */
interface PrimaryBackendInterface
{
    /**
     * Return PrimaryBackendResultObject or null if not found
     *
     * @param string $slug
     * @param array  $requestedParts
     *
     * @return PrimaryBackendObject|null
     */
    public function fetchOne($slug, array $requestedParts);

    /**
     * Return array (empty if not found)
     *
     * @param FetchParameters|FindParameters $params
     * @param array          $requestedParts
     *
     * @return PrimaryBackendResultsObject
     */
    public function fetchAll(FetchParameters $params, array $requestedParts);

    /**
     *
     * @param array $requestedParts
     * 
     * @return PrimaryBackendResultsObject
     */
    public function getNew(array $requestedParts);
	
	/**
     * Save primary backend object
     *
     * @param PrimaryBackendObject $object
     * @param array                $saveParts
     *
     * @return mixed
     */
    public function save(PrimaryBackendObject $object, array $saveParts);

    /**
     * @param PrimaryBackendObject[] $objects
     * @param array $saveParts
     *
     * @return mixed
     */
    public function saveAll(array $objects, array $saveParts);
}