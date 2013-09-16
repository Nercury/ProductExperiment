<?php
/*
 * Copyright (c) 2013 Evispa Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Darius Krištapavičius <darius@evispa.lt>
 */

namespace Evispa\ResourceApiBundle\Unicorn;

use Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface;
use Evispa\ResourceApiBundle\Exception\ResourceRequestException;

/**
 * Class UnicornSecondaryBackend
 *
 * @package Evispa\ResourceApiBundle\Backend
 */
class UnicornSecondaryBackend
{
    private $id;

    /**
     * @var array
     */
    private $managedParts = array();

    /**
     * @var SecondaryBackendInterface
     */
    private $backend = null;

    public function __construct($id, array $managedParts, SecondaryBackendInterface $backend)
    {
        $this->id = $id;
        $this->managedParts = $managedParts;
        $this->backend = $backend;
    }

    /**
     * @return SecondaryBackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    private function migrateIncommingObject($info, $resultPart, $options) {
        $partClass = get_class($resultPart);
        $actions = $info->getInputMigrationActions($partClass);

        if (null === $actions) {
            throw new ResourceRequestException('Backend "'.$this->id.'" has returned unknown object "'.$partClass.'".');
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
     * @param array|null $requestedParts Part array. If this is null, use all the parts.
     *
     * @return array
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

        foreach ($this->managedParts as $part => $info) {
            $resultPart = isset($backendResult[$part]) ? $backendResult[$part] : null;
            if (null === $resultPart) {
                continue;
            }

            $backendResult[$part] = $this->migrateIncommingObject($info, $resultPart, $options);
        }

        // If it returns a result, it must contain all of the requested parts and correct objects.

        return $backendResult;
    }

    public function fetchBySlugs($slugs, $options = array(), $requestedParts = null) {
        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new ResourceRequestException(
                        'Requested part "'.$partName.'" is not managed by "'.$this->id.'" backend.'
                    );
                }
            }
        }

        $backendResults = $this->backend->fetchBySlugs($slugs, $requestedParts);

        // If it returns a result, it must contain all of the requested parts and correct objects.

        if (null === $backendResults) {
            throw new ResourceRequestException('Backend "'.$this->id.'" has returned nothing, array expected.');
        }

        foreach ($backendResults as &$backendResult) {
            foreach ($this->managedParts as $part => $info) {
                $resultPart = isset($backendResult[$part]) ? $backendResult[$part] : null;
                if (null === $resultPart) {
                    continue;
                }

                $backendResult[$part] = $this->migrateIncommingObject($info, $resultPart, $options);
            }
        }

        return $backendResults;
    }

    public function getNew($options = array(), $requestedParts = null) {
        if (null === $requestedParts) {

            $requestedParts = array_keys($this->managedParts);

        } else {

            // Make sure all requested parts exist in managed parts, otherwise bad things can happen.

            foreach ($requestedParts as $partName) {
                if (!isset($this->managedParts[$partName])) {
                    throw new ResourceRequestException(
                        'Requested part "'.$partName.'" is not managed by "'.$this->id.'" backend.'
                    );
                }
            }
        }

        $backendResult = $this->backend->getNew($requestedParts);

        if (null === $backendResult) {
            return null;
        }

        foreach ($this->managedParts as $part => $info) {
            $resultPart = isset($backendResult[$part]) ? $backendResult[$part] : null;
            if (null === $resultPart) {
                continue;
            }

            $backendResult[$part] = $this->migrateIncommingObject($info, $resultPart, $options);
        }

        return $backendResult;
    }
}
