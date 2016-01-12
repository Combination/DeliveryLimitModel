<?php

namespace ReenExe\DeliveryLimitModel;

/**
 * @link https://docs.python.org/2/library/collections.html#collections.defaultdict
 */
class DefaultDictionary implements \ArrayAccess
{
    private $data = [];

    private $default;

    public function __construct($default)
    {
        $this->default = $default;
    }

    public function offsetExists($offset){}

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : $this->default;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset){}
}
