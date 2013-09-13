<?php

namespace Evispa\ResourceApiBundle\Unicorn;

use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;
use Evispa\ResourceApiBundle\Exception\ResourceRequestException;

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
     * @param $id
     * @param array $managedParts
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

    private function migrateIncommingObject($info, $resultPart, $options)
    {
        $partClass = get_class($resultPart);
        $actions = $info->getInputMigrationActions($partClass);

        if (null === $actions) {
            throw new ResourceRequestException('Backend "' . $this->id . '" has returned unknown object "' . $partClass . '".');
        }

        foreach ($actions as $action) {
            $resultPart = $action->run($resultPart, $options);
        }

        return $resultPart;
    }

    /**
     * Fetch a single item from this backend.
     *
     * @param string $slug Item identifier.
     * @param array $options
     * @param array|null $requestedParts Part array. If this is null, use all the parts.
     *
     * @throws ResourceRequestException
     * @return PrimaryBackendResultObject
     */
    public function fetchOne($slug, $options = array(), $requestedParts = null)
    {

        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new ResourceRequestException(
                        'Requested part "' . $partName . '" is not managed by "' . $this->id . '" backend.'
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

    public function fetchAll(FetchParameters $params, $options = array(), $requestedParts = null)
    {

        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new ResourceRequestException(
                        'Requested part "' . $partName . '" is not managed by "' . $this->id . '" backend.'
                    );
                }
            }
        }

        $backendResults = $this->backend->fetchAll($params, $requestedParts);


        // If it returns a result, it must contain all of the requested parts and correct objects.

        if (null === $backendResults) {
            throw new ResourceRequestException('Backend "' . $this->id . '" has returned nothing, PrimaryBackendResultsObject expected.');
        } elseif (is_array($backendResults)) {
            throw new ResourceRequestException('Backend "' . $this->id . '" has returned an array, PrimaryBackendResultsObject expected.');
        }

        foreach ($backendResults->getObjects() as $backendResult) {
            foreach ($this->managedParts as $part => $info) {
                $resultPart = $backendResult->getPart($part);
                if (null === $resultPart) {
                    continue;
                }

                $backendResult->setPart($part, $this->migrateIncommingObject($info, $resultPart, $options));
            }
        }

        return $backendResults;
    }

    public function getNew($options = array(), $requestedParts = null)
    {
        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new ResourceRequestException(
                        'Requested part "' . $partName . '" is not managed by "' . $this->id . '" backend.'
                    );
                }
            }
        }

        $backendResult = $this->backend->getNew($requestedParts);

        if (null === $backendResult) {
            return null;
        }

        foreach ($this->managedParts as $part => $info) {
            $resultPart = $backendResult->getPart($part);
            if (null === $resultPart) {
                continue;
            }

            $backendResult->setPart($part, $this->migrateIncommingObject($info, $resultPart, $options));
        }

        return $backendResult;
    }
}
