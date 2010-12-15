<?php
/***************************************************************************
 *   Copyright (C) 2010 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 *
	 * @see http://ru.php.net/memcached
	 * @see http://pecl.php.net/package/memcached
	 *
	 * @ingroup Cache
	**/
	final class PeclMemcached extends BaseMemcache
	{
		/**
		 * @var Memcached
		 */
		private $instance = null;
		
		/**
		 * @return PeclMemcached
		**/
		public static function create(
			$host = BaseMemcache::DEFAULT_HOST,
			$port = BaseMemcache::DEFAULT_PORT,
			$persistentId = null
		)
		{
			return new self($persistentId, $host, $port);
		}

		public function __construct(
			$host = BaseMemcache::DEFAULT_HOST,
			$port = BaseMemcache::DEFAULT_PORT,
			$persistentId = null
		)
		{
			$this->instance = new Memcached($persistentId);
			$this->instance->addServer($host, $port);

			$this->alive = true;
		}

		public function __destruct()
		{
			$this->instance = null;

			$this->alive = false;
		}

		/**
		 * @return PeclMemcached
		 */
		public function enableCompression()
		{
			parent::enableCompression();

			$this->instance->setOption(Memcached::OPT_COMPRESSION, true);

			return $this;
		}

		/**
		 * @return PeclMemcached
		**/
		public function disableCompression()
		{
			parent::disableCompression();

			$this->instance->setOption(Memcached::OPT_COMPRESSION, false);

			return $this;
		}

		/**
		 * @return PeclMemcached
		**/
		public function clean()
		{
			$this->instance->flush();

			return $this;
		}

		public function increment($key, $value)
		{
			return $this->instance->increment($key, $value);
		}

		public function decrement($key, $value)
		{
			return $this->instance->decrement($key, $value);
		}

		public function getList($indexes)
		{
			$result = $this->instance->getMulti($indexes);

			if ($result !== false)
				return $result;

			return array();
		}

		public function get($index)
		{
			$result = $this->instance->get($index);
			
			if ($result !== false)
				return $result;
			
			if ($this->instance->getResultCode() == Memcached::RES_NOTFOUND)
				return null;

			return false;
		}

		public function delete($index)
		{
			$this->instance->delete($index);
		}

		public function append($key, $data)
		{
			$this->instance->append($key, $data);
		}

		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return $this->instance->$action($key, $value, $expires);
		}
	}
?>