<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Manager;

class ResourceDataView implements \ArrayAccess
{
    private $combinedDataArray;
    private $partId;

    public function __construct($combinedDataArray, $partId)
    {
        $this->combinedDataArray = $combinedDataArray;
        $this->partId = $partId;
    }

    public function offsetExists($offset)
    {
        return isset($this->combinedDataArray[$this->partId][$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->combinedDataArray[$this->partId][$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->combinedDataArray[$this->partId][$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->combinedDataArray[$this->partId][$offset]);
    }
}
