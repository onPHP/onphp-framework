<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich, Konstantin V. Arkhipov     *
 *   noiselist@pochta.ru, voxus@gentoo.org                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/*
	CachePeer:
	
		get from cache:

			abstract public function get($key)
			
		uncache:
		
			abstract public function delete($key)

		drop everything from cache:
		
			abstract public function clean()
	
		store this data:
		
			public function set($key, $value, $expires = Cache::EXPIRES_MINIMUM)

		store this data, but only if peer *doesn't* already
		hold data for this key:
		
			public function add($key, $value, $expires = Cache::EXPIRES_MINIMUM)
		
		store this data, but only if the server *does* already
		hold data for this key:
		
			public function replace($key, $value, $expires = )

		drop object from cache:

			abstract public function delete($key)
		
		check if cache alive:
		
			abstract public function isAlive()

		drop everything
		
			abstract public function clean() 

	Memcached <- CachePeer:
	
		public function __construct(
			$host = Memcached::DEFAULT_PORT,
			$port = Memcached::DEFAULT_HOST,
			$buffer = Memcached::DEFAULT_BUFFER
		)
	
	RubberFileSystem <- CachePeer:
	
		very simple fileSystem cache
	
		public function __construct(
			$directory = '/tmp/onPHP/'
		)
	
	PlainDatabase <- CachePeer:
	
		just an example, do not ever use it even for your homePage
		(and yes, i won't provide SQL schema of used table for you)
		&& btw, - YANETUT ;-)
	
		public function __construct(
			DB $db, $tableName
		)
	
	RuntimeMemory <- CachePeer:
	
		useful for cache fallback, when all other's peers are dead
		
		public function __construct()
*/

	/**
	 * Abstract cache peer base class.
	**/
	abstract class CachePeer
	{
		protected $alive		= false;
		protected $compress		= false;
		protected $className	= null;

		abstract public function get($key);
		abstract public function delete($key);
		abstract public function clean();

		abstract protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MEDIUM
		);

		public function mark($className)
		{
			$this->className = $className;
			return $this;
		}
	
		public function set($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('set', $key, $value, $expires);
		} 
		
		public function add($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('add', $key, $value, $expires);
		} 
		
		public function replace($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->store('replace', $key, $value, $expires);
		}

		public function isAlive()
		{
			return $this->alive;
		}
		
		public function enableCompression()
		{
			$this->compress = true;
			return $this;
		}

		public function disableCompression()
		{
			$this->compress = false;
			return $this;
		}

		protected function prepareData(&$value)
		{
			if ($this->compress)
				return gzcompress(serialize($value));
			else
				return serialize($value);
		}
		
		protected function restoreData(&$value)
		{
			if ($this->compress)
				return unserialize(gzuncompress($value));
			else
				return unserialize($value);
		}
	}
?>