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

namespace Evispa\Api\Product\Model\Text;

/**
 * @Evispa\ObjectMigration\Annotations\Version("vnd.evispa.product.l11n.text.v1")
 */
class LocalizedTextV1
{
    /**
     * Array of localized text objects.
     *
     * Array key is the locale string.
     *
     * @JMS\Serializer\Annotation\Type("array<string, Evispa\Api\Product\Model\Text\TextV1>")
     * @JMS\Serializer\Annotation\XmlMap
     * @JMS\Serializer\Annotation\XmlKeyValuePairs
     *
     * @var array
     */
    public $items = array();

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(from="Evispa\Api\Product\Model\Text\TextV1", require={"locale"})
     *
     * @param TextV1 $old Old version of text part.
     *
     * @return self
     */
    public static function fromTextV1(TextV1 $other, $options) {
        $locale = $options['locale'];

        $new = new self();

        $new->items[$locale] = clone $other;

        return $new;
    }

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(to="Evispa\Api\Product\Model\Text\TextV1", require={"locale"})
     *
     * @return TextV1
     */
    public function toTextV1($options) {
        $locale = $options['locale'];

        if (isset($this->items[$locale])) {
            return $this->items[$locale];
        }

        return new TextV1();
    }
}