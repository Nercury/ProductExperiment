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

namespace Evispa\ResourceApiBundle\Tests\Mock;


use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendObject;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultsObject;

class MockPrimaryProductBackend implements PrimaryBackendInterface
{

    public $findOneResult = array();
    public $findResult = array();

    public function fetchOne($slug, array $requestedParts)
    {
        if (isset($this->findOneResult[$slug]) === true) {
            return $this->findOneResult[$slug];
        }

        return null;
    }

    public function fetchAll(FetchParameters $params, array $requestedParts)
    {
        return $this->findResult;
    }

    /**
     *
     * @param array $requestedParts
     *
     * @return PrimaryBackendResultsObject
     */
    public function getNew(array $requestedParts)
    {
        // TODO: Implement getNew() method.
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
