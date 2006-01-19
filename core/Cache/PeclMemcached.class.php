<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Connector for PECL's Memcache extension by Antony Dovgal.
	 *
	 * @see http://tony2001.phpclub.net/
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
				$this->instance->connect($host, $port);
				$this->alive = true;
			} catch (BaseException $e) {
				return null;
			}
		}
		
		public function __destruct()
		{
			if ($this->instance)
				$this->instance->close();
		}
		
		public function clean()
		{
			$this->instance->flush();

			return $this;
		}
		
		public function get($index)
		{
			if ($result = $this->instance->get($index))
				return $this->restoreData($result);
			else
				return null;
		}
		
		public function delete($index)
		{
			return $this->instance->delete($index);
		}
		
		protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return
				$this->instance->$action(
					$key,
					$this->prepareData($value),
					false,
					$expires
				);
		}
	}
?>