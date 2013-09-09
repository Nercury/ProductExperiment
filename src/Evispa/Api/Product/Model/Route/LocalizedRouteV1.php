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
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\Api\Product\Model\Route;

/**
 * Object route information.
 *
 * This object stores public route "slug" for the object. Note that it is not a link,
 * just a piece of information about route identifier.
 *
 * @Evispa\ObjectMigration\Annotations\Version("vnd.evispa.l11n-route.v1")
 */
class LocalizedRouteV1
{
    /**
     * Array of localized route slugs.
     *
     * Array key is the locale string.
     *
     * @JMS\Serializer\Annotation\Type("array<string, string>")
     * @JMS\Serializer\Annotation\XmlKeyValuePairs
     * @JMS\Serializer\Annotation\XmlList(inline = true)
     *
     * @var string
     */
    public $items = array();

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(from="Evispa\Api\Product\Model\Route\RouteV1", require={"locale"})
     *
     * @param TextV1 $old Old version of text part.
     *
     * @return self
     */
    public static function fromRouteV1(RouteV1 $other, $options) {
        $locale = $options['locale'];

        $new = new self();

        $new->items[$locale] = $other->slug;

        return $new;
    }

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(to="Evispa\Api\Product\Model\Route\RouteV1", require={"locale"})
     *
     * @return TextV1
     */
    public function toRouteV1($options) {
        $locale = $options['locale'];

        if (isset($this->items[$locale])) {
            $result = new RouteV1();
            $result->slug = $this->items[$locale];
            return $result;
        }

        return new RouteV1();
    }
}