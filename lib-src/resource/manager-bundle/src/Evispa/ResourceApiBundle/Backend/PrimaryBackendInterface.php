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
     * @return PrimaryBackendResultObject|null
     */
    public function findOne($slug, array $requestedParts);

    /**
     * Return array (empty if not found)
     *
     * @param FindParameters $params
     * @param array          $requestedParts
     *
     * @return PrimaryBackendResultsObject
     */
    public function find(FindParameters $params, array $requestedParts);
}