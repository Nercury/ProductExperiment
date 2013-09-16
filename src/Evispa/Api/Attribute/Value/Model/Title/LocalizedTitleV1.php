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
 * @author Tadas Gliaubicas <tadas@evispa.lt>
 */

namespace Evispa\Api\Attribute\Value\Model\Title;

/**
 * @Evispa\ObjectMigration\Annotations\Version("vnd.evispa.attribute-value.i18n-title.v1")
 */
class LocalizedTitleV1
{
    /**
     * Array of localized title objects.
     *
     * Array key is the locale string.
     *
     * @JMS\Serializer\Annotation\Type("array<string, Evispa\Api\Attribute\Value\Model\Title\TitleV1>")
     * @JMS\Serializer\Annotation\XmlKeyValuePairs
     * @JMS\Serializer\Annotation\XmlList(inline = true)
     *
     * @var TitleV1[]
     */
    public $items = array();

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(
     *      from="Evispa\Api\Attribute\Value\Model\Title\TitleV1",
     *      require={"locale"}
     * )
     *
     * @param TitleV1 $other
     * @param $options
     *
     * @return LocalizedTitleV1
     */
    public static function fromTitleV1(TitleV1 $other, $options)
    {
        $locale = $options['locale'];

        $new = new self();

        $new->items[$locale] = clone $other;

        return $new;
    }

    /**
     * @Evispa\ObjectMigration\Annotations\Migration(
     *      to="Evispa\Api\Attribute\Value\Model\Title\TitleV1",
     *      require={"locale"}
     * )
     *
     * @param $options
     * @return TitleV1
     */
    public function toTitleV1($options)
    {
        $locale = $options['locale'];

        if (isset($this->items[$locale])) {
            return clone $this->items[$locale];
        }

        return new TitleV1();
    }
}
