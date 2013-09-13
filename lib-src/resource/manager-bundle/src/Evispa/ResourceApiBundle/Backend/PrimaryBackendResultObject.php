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

namespace Evispa\ResourceApiBundle\Backend;

/**
 * Class PrimaryBackendResultObject
 *
 * @package Evispa\ResourceApiBundle\Backend
 */
class PrimaryBackendResultObject
{
    /**
     * @var String
     */
    private $resourceSlug;

    /**
     * @var array
     */
    private $resourceParts = array();

    /**
     * @param $resourceSlug
     */
    public function __construct($resourceSlug)
    {
        if (strlen($resourceSlug) > 24) {
            throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException('Resource slug length can not exceed 24 symbols.');
        }

        $this->resourceSlug = $resourceSlug;
    }

    /**
     * Get resource parts.
     *
     * @return mixed[]
     */
    public function getResourceParts()
    {
        return $this->resourceParts;
    }

    /**
     * Get resource object slug.
     *
     * @return String
     */
    public function getResourceSlug()
    {
        return $this->resourceSlug;
    }

    /**
     * Add a part to result object.
     *
     * @param string $partName Part name.
     * @param mixed $part Part object.
     */
    public function setPart($partName, $part)
    {
        $this->resourceParts[$partName] = $part;
    }

    /**
     * Get part.
     *
     * @param string $partName Part name.
     *
     * @return mixed
     */
    public function getPart($partName) {
        return isset($this->resourceParts[$partName])
            ? $this->resourceParts[$partName]
            : null;
    }

    /**
     * Check if specified part is set.
     *
     * @param string $partName Part name.
     *
     * @return boolean
     */
    public function hasPart($partName) {
        return isset($this->resourceParts[$partName]);
    }
}
