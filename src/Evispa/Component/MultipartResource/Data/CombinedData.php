<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Data;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlKeyValuePairs;

/**
 * Contains all the data required to inspect the resource and modify it.
 */
class CombinedData implements \ArrayAccess
{
    /**
     * @Type("array")
     * @XmlList(inline = true)
     * @XmlKeyValuePairs
     */
    private $items;

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
