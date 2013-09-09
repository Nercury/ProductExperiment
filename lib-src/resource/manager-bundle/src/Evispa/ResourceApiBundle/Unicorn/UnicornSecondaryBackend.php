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

    /**
     * Fetch a single item from this backend.
     *
     * @param string $slug Item identifier.
     * @param array|null $requestedParts Part array. If this is null, use all the parts.
     *
     * @return array
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

    public function fetchBySlugs($slugs, $requestedParts = null) {
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
        
        $backendResults = $this->backend->fetchBySlugs($slugs, $requestedParts);
        
        // If it returns a result, it must contain all of the requested parts and correct objects.
        
        foreach ($backendResults->getObjects() as $backendResult) {
            $this->validateResultItem($backendResult, $requestedParts);
        }
        
        return $backendResults;
    }
    
    /**
     * Throw an exception if a result item contains something bad.
     *
     * @param array $item Result item.
     * @param string[] $requestedParts Array of requested parts.
     */
    private function validateResultItem($item, $requestedParts) {
        
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
