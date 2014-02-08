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

final class RedisNoSQLList implements Listable
{
	private $redis		= null;
	private $key		= null;
	private $position	= null;
	private $timeout	= null;

	public function __construct(Redis $redis, $key, $timeout = null)
	{
		$this->redis	= $redis;
		$this->key		= $key;
		$this->timeout	= $timeout;
	}

	/**
	 * @param mixed $value
	 * @return RedisList
	 */
	public function append($value)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->redis->rpush($this->key, $value);

		if ($this->timeout)
			$this->redis->setTimeout($this->key, $this->timeout);

        $profiling->setInfo('rpush ' . $this->key)->end();
		return $this;
	}

	/**
	 * @param mixed $value
	 * @return RedisList
	 */
	public function prepend($value)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->redis->lpush($this->key, $value);

		if ($this->timeout)
			$this->redis->setTimeout($this->key, $this->timeout);

        $profiling->setInfo('lpush ' . $this->key)->end();
		return $this;
	}

	/**
	 * @return RedisList
	 */
	public function clear()
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->redis->LTrim($this->key, -1, 0);

        $profiling->setInfo('LTrim ' . $this->key)->end();
		return $this;
	}


	public function count()
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
        $result = $this->redis->lsize($this->key);
        $profiling->setInfo('lsize ' . $this->key)->end();
        return $result;
	}

	public function pop()
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
        $result = $this->redis->lpop($this->key);
        $profiling->setInfo('lpop ' . $this->key)->end();
        return $result;
	}

	public function range($start, $length = null)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$end = is_null($length)
			? -1
			: $start + $length;

        $result = $this->redis->lrange($this->key, $start, $end);
        $profiling->setInfo('lrange ' . $this->key . ' ' . $start . ' ' . $end)->end();
        return $result;
	}

	public function get($index)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
        $result = $this->redis->lget($this->key, $index);
        $profiling->setInfo('lget ' . $this->key . ' ' . $index)->end();
        return $result;
	}

	public function set($index, $value)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->redis->lset($this->key, $index, $value);

		if ($this->timeout)
			$this->redis->expire($this->key, $this->timeout);

        $profiling->setInfo('lset ' . $this->key . ' ' . $index)->end();
		return $this;
	}

	public function trim($start, $length = null)
	{
		$end = is_null($length)
			? -1
			: $start + $length - 1;

		$this->redis->ltrim($this->key, $start, $end);
	}

	//region Iterator
	public function current()
	{
		return $this->get($this->position);
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->position++;
	}

	public function rewind()
	{
		$this->position = 0;
	}

	public function valid()
	{
		return $this->offsetExists($this->position);
	}
	//endregion

	//region ArrayAccess

	public function offsetExists($offset)
	{
		return false !== $this->get($offset);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		throw new UnimplementedFeatureException();
	}

	public function seek($position) {
		$this->position = $position;
	}
	//endregion
}