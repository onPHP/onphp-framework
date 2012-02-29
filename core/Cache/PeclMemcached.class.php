<?php
/***************************************************************************
 *   Copyright (C) 2012 by Kutsurua Georgy Tamazievich,                    *
 *   Andrew N. Fediushin, Konstantin V. Arkhipov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Connector for PECL's Memcached extension.
	 *
	 * @see http://pecl.php.net/package/memcached
	 *
	 * @ingroup Cache
	**/
	class PeclMemcached extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
	
		/**
		 * @var Memcached
		 */
		private $instance = null;
	
		/**
		 * @return PeclMemcached
		**/
		public static function create(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT
		)
		{
			return new self($host, $port);
		}
	
		public function __construct(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT
		)
		{
			$this->instance = new Memcached();
	
			$this->instance->addServer($host, $port);
			$this->instance->setOption(Memcached::OPT_COMPRESSION, false);
			$this->instance->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
			$this->instance->setOption(Memcached::OPT_BUFFER_WRITES, false);
			$this->instance->setOption(Memcached::OPT_NO_BLOCK, false);
	
			$this->alive = is_array($this->instance->getVersion());
		}
	
		public function __destruct()
		{
	
		}
	
		/**
		 * Clean cache, equal restart memcache server
		 * @return PeclMemcached
		**/
		public function clean()
		{
			try {
				$this->instance->flush();
			} catch (BaseException $e) {
				$this->alive = false;
			}
	
			return parent::clean();
		}
	
		/**
		 * @return PeclMemcached
		 **/
		public function enableCompression()
		{
			$this->compress = true;
			$this->instance->setOption(Memcached::OPT_COMPRESSION, true);
	
			return $this;
		}
	
		/**
		 * @return PeclMemcached
		 **/
		public function disableCompression()
		{
			$this->compress = false;
			$this->instance->setOption(Memcached::OPT_COMPRESSION, false);
	
			return $this;
		}
	
		public function increment($key, $value)
		{
			try {
				return $this->instance->increment($key, $value);
			} catch (BaseException $e) {
				$this->alive = false;
				return null;
			}
		}
	
		public function decrement($key, $value)
		{
			try {
				return $this->instance->decrement($key, $value);
			} catch (BaseException $e) {
				$this->alive = false;
				return null;
			}
		}
	
		public function getList($indexes)
		{
			try {
				return $this->instance->getMulti(
					$indexes,
					$cas/*,
						Why ?
					Memcached::GET_PRESERVE_ORDER
					*/
				);
			} catch (BaseException $e) {
				$this->alive = false;
				return array();
			}
	
			Assert::isUnreachable();
		}
	
		public function get($index)
		{
			try {
				return $this->instance->get($index);
			} catch (BaseException $e) {
				$this->alive = false;
				return null;
			}
	
			Assert::isUnreachable();
		}
	
		public function delete($index)
		{
			try {
				return $this->instance->delete($index, 0);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
	
			Assert::isUnreachable();
		}
	
		public function append($key, $data)
		{
			try {
				return $this->instance->append($key, $data);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
	
			Assert::isUnreachable();
		}
	
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			try {
				return
					$this->instance->$action(
						$key,
						$value,
						$expires
					);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
	
			Assert::isUnreachable();
		}
	}
?>