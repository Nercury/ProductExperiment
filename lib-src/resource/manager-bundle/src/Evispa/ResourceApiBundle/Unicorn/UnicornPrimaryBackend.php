<?php

namespace Evispa\ResourceApiBundle\Unicorn;

use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;

/**
 * @author nerijus
 */
class UnicornPrimaryBackend
{
    private $id;

    /**
     * @var array
     */
    private $managedParts = array();

    /**
     * @var PrimaryBackendInterface
     */
    private $backend = null;

    /**
     * @param array                   $managedParts
     * @param PrimaryBackendInterface $backend
     */
    public function __construct($id, array $managedParts, PrimaryBackendInterface $backend)
    {
        $this->id = $id;
        $this->managedParts = $managedParts;
        $this->backend = $backend;
    }

    /**
     * @return PrimaryBackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Fetch a single item from this backend.
     *
     * @param string $slug Item identifier.
     * @param array|null $requestedParts Part array. If this is null, use all the parts.
     *
     * @return \Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject
     */
    public function fetchOne($slug, $requestedParts = null) {

        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException(
                        'Requested part "'.$partName.'" is not managed by "'.$this->id.'" backend.'
                    );
                }
            }
        }

        $backendResult = $this->backend->fetchOne($slug, $requestedParts);


        // If primary backend returns null, it means that item does not exist in database.

        if (null === $backendResult) {
            return null;
        }

        // If it returns a result, it must contain all of the requested parts and correct objects.

        $this->validateResultItem($backendResult, $requestedParts);

        return $backendResult;
    }

    public function fetchAll(\Evispa\ResourceApiBundle\Backend\FetchParameters $params, $requestedParts = null) {

        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException(
                        'Requested part "'.$partName.'" is not managed by "'.$this->id.'" backend.'
                    );
                }
            }
        }

        $backendResults = $this->backend->fetchAll($params, $requestedParts);

        // If it returns a result, it must contain all of the requested parts and correct objects.

        foreach ($backendResults->getObjects() as $backendResult) {
            $this->validateResultItem($backendResult, $requestedParts);
        }

        return $backendResults;
    }

    /**
     * Throw an exception if a result item contains something bad.
     *
     * @param \Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject $item Result item.
     * @param string[] $requestedParts Array of requested parts.
     */
    private function validateResultItem(\Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject $item, $requestedParts) {
        if (null === $item->getResourceSlug() || false === $item->getResourceSlug() || '' === $item->getResourceSlug()) {
            throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException(
                'Backend "'.$this->id.'" should not return results with empty slug.'
            );
        }

        foreach ($requestedParts as $partName) {

            $part = $item->getPart($partName);

            if (null !== $part) {
                $partClassName = get_class($part);

                if ($this->managedParts[$partName] !== $partClassName) {
                    throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException(
                        'Backend "'.$this->id.'" has returned an incorrect object for "'.$partName.'" part. '.
                        'Expected "'.$this->managedParts[$partName].'", but got "'.$partClassName.'".'
                    );
                }
            }
        }
    }
}
