<?php
/**
 * @author nerijus
 */

namespace Evispa\Resource\Component\MultipartResource\Data;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlKeyValuePairs;

/**
 * Contains all the data required to inspect the resource and modify it.
 */
class CombinedData implements \ArrayAccess
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @Type("array")
     * @XmlList(inline = true)
     * @XmlKeyValuePairs
     */
    private $data;

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
