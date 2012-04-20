<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Connector for PECL's Memcache extension by Antony Dovgal.
	 *
	 * @see http://tony2001.phpclub.net/
	 * @see http://pecl.php.net/package/memcache
	 *
	 * @ingroup Cache
	**/
	class PeclMemcached extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
		const DEFAULT_TIMEOUT	= 1;
		
		private $instance = null;
		private $requestTimeout = null;
		private $connectTimeout = null;
		private $host = null;
		private $port = null;
		private $triedConnect = false;
		
		/**
		 * @return PeclMemcached
		**/
		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$connectTimeout = PeclMemcached::DEFAULT_TIMEOUT
		)
		{
			return new self($host, $port, $connectTimeout);
		}
		
		public function __construct(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$connectTimeout = PeclMemcached::DEFAULT_TIMEOUT
		)
		{
			$this->host = $host;
			$this->port = $port;
			$this->connectTimeout = $connectTimeout;
		}
		
		public function __destruct()
		{
			if ($this->alive) {
				try {
					$this->instance->close();
				} catch (BaseException $e) {
					// shhhh.
				}
			}
		}
		
		public function isAlive()
		{
			$this->ensureConnected();
			
			return parent::isAlive();
		}
		
		/**
		 * @return PeclMemcached
		**/
		public function clean()
		{
			$this->ensureConnected();
			
			try {
				$this->instance->flush();
			} catch (BaseException $e) {
				$this->alive = false;
			}
			
			return parent::clean();
		}
		
		public function increment($key, $value)
		{
			$this->ensureConnected();
			
			try {
				return $this->instance->increment($key, $value);
			} catch (BaseException $e) {
				return null;
			}
		}
		
		public function decrement($key, $value)
		{
			$this->ensureConnected();
			
			try {
				return $this->instance->decrement($key, $value);
			} catch (BaseException $e) {
				return null;
			}
		}
		
		public function getList($indexes)
		{
			$this->ensureConnected();
			
			return
				($return = $this->get($indexes))
					? $return
					: array();
		}
		
		public function get($index)
		{
			$this->ensureConnected();
			
			try {
				return $this->instance->get($index);
			} catch (BaseException $e) {
				if(strpos($e->getMessage(), 'Invalid key') !== false)
					return null;
				
				$this->alive = false;
				
				return null;
			}
			
			Assert::isUnreachable();
		}
		
		public function delete($index)
		{
			$this->ensureConnected();
			
			try {
				// second parameter required, wrt new memcached protocol:
				// delete key 0 (see process_delete_command in the memcached.c)
				// Warning: it is workaround!
				return $this->instance->delete($index, 0);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
		public function append($key, $data)
		{
			$this->ensureConnected();
			
			try {
				return $this->instance->append($key, $data);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
		/**
		 * @param float $requestTimeout time in seconds
		 * @return \PeclMemcached 
		 */
		public function setTimeout($requestTimeout)
		{
			$this->ensureConnected();
			$this->requestTimeout = $requestTimeout;
			$this->instance->setServerParams($this->host, $this->port, $requestTimeout);
			
			return $this;
		}
		
		/**
		 * @return float 
		 */
		public function getTimeout()
		{
			return $this->requestTimeout;
		}
		
		protected function ensureConnected() {
			if ($this->triedConnect) 
				return $this;
			
			$this->triedConnect = true;
			$this->instance = new Memcache();
			
			try {
				
				try {
					$this->instance->pconnect($this->host, $this->port, $this->connectTimeout);
				} catch (BaseException $e) {
					$this->instance->connect($this->host, $this->port, $this->connectTimeout);
				}
				
				$this->alive = true;
				$this->setTimeout($this->connectTimeout);
				
			} catch (BaseException $e) {
				// bad luck.
			}
			
			return $this;
		}
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$this->ensureConnected();
			try {
				return
					$this->instance->$action(
						$key,
						$value,
						$this->compress
							? MEMCACHE_COMPRESSED
							: false,
						$expires
					);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
	}
?>