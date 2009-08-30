<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
	final class PeclMemcached extends CachePeer
	{
		const DEFAULT_PORT		= 11211;
		const DEFAULT_HOST		= '127.0.0.1';
		
		private $instance = null;

		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT
		)
		{
			return new PeclMemcached($host, $port);
		}
		
		public function __construct(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT
		)
		{
			$this->instance = new Memcache();
			
			try {
				try {
					$this->instance->pconnect($host, $port);
				} catch (BaseException $e) {
					$this->instance->connect($host, $port);
				}
				
				$this->alive = true;
			} catch (BaseException $e) {
				// bad luck.
			}
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
		
		public function clean()
		{
			try {
				$this->instance->flush();
			} catch (BaseException $e) {
				$this->alive = false;
			}

			return $this;
		}
		
		public function get($index)
		{
			try {
				$result = $this->instance->get($index);
			} catch (BaseException $e) {
				$this->alive = false;
				
				return null;
			}
			
			return $result;
		}
		
		public function delete($index)
		{
			try {
				return $this->instance->delete($index);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			/* NOTREACHED */
		}
		
		protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
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
			
			/* NOTREACHED */
		}
	}
?>