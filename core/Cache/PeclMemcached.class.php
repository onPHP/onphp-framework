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
			$persistentId = null,
			array $serverList = array()
		)
		{
			return new self($persistentId, $serverList);
		}

		/**
		 * @see http://ru2.php.net/manual/en/memcached.addservers.php
		 */
		public function __construct(
			$persistentId = null,
			array $serverList = array()
		)
		{
			$this->instance = new Memcached($persistentId);

			if (!$serverList)
				$this->instance->addServer(
					BaseMemcache::DEFAULT_HOST,
					BaseMemcache::DEFAULT_PORT
				);
			else
				$this->instance->addServers($serverList);

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
			$this->instance->setOption(Memcached::OPT_COMPRESSION, true);

			parent::enableCompression();

			return $this;
		}

		/**
		 * @return PeclMemcached
		**/
		public function disableCompression()
		{
			$this->instance->setOption(Memcached::OPT_COMPRESSION, false);
			
			parent::disableCompression();

			return $this;
		}

		/**
		 * @return PeclMemcached
		**/
		public function clean()
		{
			$this->instance->flush();
			
			$this->checkState();

			return parent::clean();
		}

		public function increment($key, $value)
		{
			$result = $this->instance->increment($key, $value);

			$this->checkState();

			return $result;
		}

		public function decrement($key, $value)
		{
			$result = $this->instance->decrement($key, $value);

			$this->checkState();

			return $result;
		}

		public function getList($indexes)
		{
			$result = $this->instance->getMulti($indexes);

			$this->checkState();

			if ($result !== false)
				return $result;

			return array();
		}

		public function get($index)
		{
			$result = $this->instance->get($index);
			
			$this->checkState();

			if ($result !== false)
				return $result;
			
			if ($this->instance->getResultCode() == Memcached::RES_NOTFOUND)
				return null;

			return false;
		}

		public function delete($index)
		{
			$result = $this->instance->delete($index);

			$this->checkState();

			return $result;
		}

		public function append($key, $data)
		{
			$result = $this->instance->append($key, $data);

			$this->checkState();

			return $result;
		}

		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$result = $this->instance->$action($key, $value, $expires);

			$this->checkState();

			return $result;
		}

		private function checkState()
		{
			$code = $this->instance->getResultCode();

			switch ($code) {
				case Memcached::RES_NO_SERVERS :
				case Memcached::RES_SOME_ERRORS :
				case Memcached::RES_SERVER_ERROR :
				case Memcached::RES_PROTOCOL_ERROR :

					$this->alive = false;
					return false;

					break;

				default :
					return true;
			}

			Assert::isUnreachable();
		}
	}
?>