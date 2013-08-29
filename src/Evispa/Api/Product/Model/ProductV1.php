<?php
/*
 * Copyright (c) 2013 Evispa Ltd. Vilnius
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
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\Api\Product\Model;

use JMS\Serializer\Annotation\Type;
use Evispa\ObjectMigration\Annotations as Api;
use Evispa\ObjectMigration\VersionConverter;

/**
 * @Api\Version("vnd.evispa.product.v1")
 */
class ProductV1 implements \Evispa\Api\Resource\Model\ApiResourceInterface
{
    private $slug;

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @Type("Evispa\Api\Product\Model\Code\ProductCodeV1")
     * @Api\VersionedProperty("Evispa\Api\Product\Model\Code\ProductCodeV1")
     *
     * @var Code\ProductCodeV1
     */
    public $code = null;

    /**
     * @Type("Evispa\Api\Product\Model\Text\LocalizedTextV1")
     * @Api\VersionedProperty("Evispa\Api\Product\Model\Text\LocalizedTextV1")
     *
     * @var Text\LocalizedTextV1
     */
    public $text = null;

    /**
     * @Api\Migration(from="Evispa\Api\Product\Model\SimpleProductV1")
     *
     * @param CodeV1 $old Old version of code part.
     *
     * @return self
     */
    public static function fromSimpleProductV1(VersionConverter $converter, CodeV1 $other) {
        $obj = new self();

        $obj->setSlug($other->getSlug());
        $converter->migrateProperty($other, 'code', $obj, 'code');
        $converter->migrateProperty($other, 'text', $obj, 'text');

        return $obj;
    }

    /**
     * @Api\Migration(from="Evispa\Api\Product\Model\SimpleProductV1")
     *
     * @param CodeV1 $old Old version of code part.
     *
     * @return self
     */
    public function toSimpleProductV1(VersionConverter $converter) {
        $obj = new SimpleProductV1();

        $obj->setSlug($this->getSlug());
        $converter->migrateProperty($this, 'code', $obj, 'code');
        $converter->migrateProperty($this, 'text', $obj, 'text');

        return $obj;
    }
}