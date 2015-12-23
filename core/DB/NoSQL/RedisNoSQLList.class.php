<?php

/***************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class RedisNoSQLList implements Listable
{
    private $redis = null;
    private $key = null;
    private $position = null;
    private $timeout = null;

    /**
     * RedisNoSQLList constructor.
     * @param Redis $redis
     * @param $key
     * @param null $timeout
     */
    public function __construct(Redis $redis, $key, $timeout = null)
    {
        $this->redis = $redis;
        $this->key = $key;
        $this->timeout = $timeout;
    }

    /**
     * @param $value
     * @return RedisNoSQLList
     */
    public function append($value) : RedisNoSQLList
    {
        $this->redis->rpush($this->key, $value);

        if ($this->timeout)
            $this->redis->setTimeout($this->key, $this->timeout);

        return $this;
    }

    /**
     * @param $value
     * @return RedisNoSQLList
     */
    public function prepend($value) : RedisNoSQLList
    {
        $this->redis->lpush($this->key, $value);

        if ($this->timeout)
            $this->redis->setTimeout($this->key, $this->timeout);

        return $this;
    }

    /**
     * @return RedisNoSQLList
     */
    public function clear() : RedisNoSQLList
    {
        $this->redis->LTrim($this->key, -1, 0);

        return $this;
    }


    /**
     * @return int
     */
    public function count()
    {
        return $this->redis->lLen($this->key);
    }

    /**
     * @return string
     */
    public function pop()
    {
        return $this->redis->lpop($this->key);
    }

    /**
     * @param $start
     * @param null $length
     * @return array
     */
    public function range($start, $length = null)
    {
        $end = is_null($length)
            ? -1
            : $start + $length;

        return $this->redis->lrange($this->key, $start, $end);
    }

    /**
     * @param $index
     * @return String
     */
    public function get($index)
    {
        return $this->redis->lIndex($this->key, $index);
    }

    /**
     * @param $index
     * @param $value
     * @return $this
     */
    public function set($index, $value)
    {
        $this->redis->lset($this->key, $index, $value);

        if ($this->timeout)
            $this->redis->expire($this->key, $this->timeout);

        return $this;
    }

    /**
     * @param $start
     * @param null $length
     * @return Listable|void
     */
    public function trim($start, $length = null)
    {
        $end = is_null($length)
            ? -1
            : $start + $length - 1;

        $this->redis->ltrim($this->key, $start, $end);
    }

    /**
     * @return String
     */
    public function current()
    {
        return $this->get($this->position);
    }

    /**
     * @return null
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * position +
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * position 0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     */
    public function valid() : bool
    {
        return $this->offsetExists($this->position);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return false !== $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @return String
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return RedisNoSQLList
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @throws UnimplementedFeatureException
     */
    public function offsetUnset($offset)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @param int $position
     */
    public function seek($position)
    {
        $this->position = $position;
    }
}
