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


class MockProduct implements \Evispa\Api\Resource\Model\ApiResourceInterface
{
    private $slug;

    /**
     * @JMS\Serializer\Annotation\Type("Evispa\ResourceApiBundle\Tests\Mock\MockProductText")
     */
    public $text;

    /**
     * @JMS\Serializer\Annotation\Type("Evispa\ResourceApiBundle\Tests\Mock\MockProductPrice")
     */
    public $price;

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(to="Evispa\ResourceApiBundle\Tests\Mock\MockSimpleProduct")
     *
     * @param $options
     *
     * @return MockSimpleProduct
     */
    public function toSimpleProduct($options)
    {
        $obj = new MockSimpleProduct();
        $obj->setSlug($this->slug);

        return $obj;
    }
}
