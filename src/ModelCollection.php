<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 13:30
 */

namespace Aw;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class ModelCollection implements \ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    protected $container = [];


    public function add(Model $m)
    {
        $this->container[] = $m;
        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->container);
    }

    public function offsetSet($offset, $value)
    {
//        var_dump($value instanceof Model);
        if (!($value instanceof Model)) {
            return;
        }
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->container);
    }

    public function toArray()
    {
        $ret = array();
        foreach ($this->container as $item) {
            $ret[] = $item->toArray();
        }
        return $ret;
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}