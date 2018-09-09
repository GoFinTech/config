<?php

namespace GoFinTech\Config;

class Config implements \ArrayAccess
{

    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function get(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->values)) {
            if (func_num_args() == 2)
                return $default;
            else
                throw new \UnexpectedValueException("Config.get: key $name is not defined");
        }
        return $this->values[$name];
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadFunctionCallException("Config instance is read-only");
    }

    public function offsetUnset($offset)
    {
        throw new \BadFunctionCallException("Config instance is read-only");
    }
}
