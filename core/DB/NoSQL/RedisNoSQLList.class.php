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
		
		public function __construct(Redis $redis, $key)
		{
			$this->redis	= $redis;
			$this->key		= $key;
		}
		
		/**
		 * @param mixed $value
		 * @return RedisList 
		 */
		public function append($value)
		{
			$this->redis->rpush($this->key, $value);
			
			return $this;
		}
		
		/**
		 * @param mixed $value
		 * @return RedisList 
		 */
		public function prepend($value)
		{
			$this->redis->lpush($this->key, $value);
			
			return $this;
		}
		
		/**
		 * @return RedisList 
		 */
		public function clear()
		{
			$this->redis->LTrim($this->key, -1, 0);
			
			return $this;
		}
		
		
		public function count()
		{
			return $this->redis->lsize($this->key);
		}
		
		public function pop()
		{
			return $this->redis->lpop($this->key);
		}
		
		public function range($start, $length = null)
		{
			$end = is_null($length)
				? -1
				: $start + $length;
			
			return $this->redis->lrange($this->key, $start, $end);
		}
		
		public function get($index)
		{
			return $this->redis->lget($this->key, $index);
		}
		
		public function set($index, $value)
		{
			$this->redis->lset($this->key, $index, $value);
			
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
