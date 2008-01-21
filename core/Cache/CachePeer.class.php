<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

/*
	CachePeer:
	
		get from cache:
		
			abstract public function get($key)
		
		multi-get from cache:
		
			abstract public function getList($keys)
		
		uncache:
		
			abstract public function delete($key)
		
		drop everything from cache:
		
			abstract public function clean()
		
		store this data:
		
			public function set(
				$key, $value, $expires = Cache::EXPIRES_MEDIUM
			)
		
		store this data, but only if peer *doesn't* already
		hold data for this key:
		
			public function add(
				$key, $value, $expires = Cache::EXPIRES_MEDIUM
			)
		
		store this data, but only if the server *does* already
		hold data for this key:
		
			public function replace(
				$key, $value, $expires = Cache::EXPIRES_MEDIUM
			)
		
		add this data to an existing key after existing data:
		
			public function append($key, $data)
		
		drop object from cache:
		
			abstract public function delete($key)
		
		check if cache alive:
		
			abstract public function isAlive()
	
	Memcached <- CachePeer:
	
		public function __construct(
			$host = Memcached::DEFAULT_PORT,
			$port = Memcached::DEFAULT_HOST,
			$buffer = Memcached::DEFAULT_BUFFER
		)
	
	PeclMemcached <- CachePeer
	
		public function __construct(
			$host = Memcached::DEFAULT_PORT,
			$port = Memcached::DEFAULT_HOST
		)
	
	RubberFileSystem <- CachePeer:
	
		very simple fileSystem cache
	
		public function __construct(
			$directory = '/tmp/onPHP/'
		)
	
	RuntimeMemory <- CachePeer:
	
		useful for cache fallback, when all other's peers are dead
		
		public function __construct()
	
	SharedMemory <- CachePeer:
	
		Sys-V shared memory, for memcachedless installations.
		
		public function __construct(
			$defaultSize = self::DEFAULT_SEGMENT_SIZE,
			$customSized = array() // 'className' => sizeInBytes
		)
*/

	/**
	 * Abstract cache peer base class.
	 * 
	 * @ingroup Cache
	**/
	abstract class CachePeer
	{
		const TIME_SWITCH		= 2592000; // 60 * 60 * 24 * 30

		protected $alive		= false;
		protected $compress		= false;

		abstract public function get($key);
		abstract public function delete($key);
		
		/**
		 * @return CachePeer
		**/
		public function clean()
		{
			foreach (Singleton::getAllInstances() as $object)
				if ($object instanceof GenericDAO)
					$object->dropIdentityMap();
			
			return $this;
		}
		
		abstract protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		);
		
		abstract public function append($key, $data);
		
		public function getList($indexes)
		{
			// intentially not array
			$out = null;
			
			foreach ($indexes as $key)
				if (null !== ($value = $this->get($key)))
					$out[] = $value;
			
			return $out;
		}
		
		final public function set($key, $value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('set', $key, $value, $expires);
		}
		
		final public function add($key, $value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('add', $key, $value, $expires);
		}
		
		final public function replace($key, $value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('replace', $key, $value, $expires);
		}

		public function isAlive()
		{
			return $this->alive;
		}
		
		/**
		 * @return CachePeer
		**/
		public function mark($className)
		{
			return $this;
		}
		
		/**
		 * @return CachePeer
		**/
		public function enableCompression()
		{
			$this->compress = true;
			return $this;
		}

		/**
		 * @return CachePeer
		**/
		public function disableCompression()
		{
			$this->compress = false;
			return $this;
		}

		protected function prepareData($value)
		{
			if ($this->compress)
				return gzcompress(serialize($value));
			else
				return serialize($value);
		}
		
		protected function restoreData($value)
		{
			if ($this->compress)
				return unserialize(gzuncompress($value));
			else
				return unserialize($value);
		}
	}
?>