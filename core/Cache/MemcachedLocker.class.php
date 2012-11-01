<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Memcached based locking.
	 * No synchronization between local pool and memcached daemons!
	 *
	 * @ingroup Lockers
	**/
	namespace Onphp;

	final class MemcachedLocker extends BaseLocker implements Instantiatable
	{
		const VALUE = 0x1;
		
		private $memcachedClient = null;

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setMemcachedClient(CachePeer $memcachedPeer)
		{
			$this->memcachedClient = $memcachedPeer;

			return $this;
		}

		public function get($key)
		{
			return $this->memcachedClient->add(
				$key,
				self::VALUE,
				2 * Cache::EXPIRES_MINIMUM
			);
		}

		public function free($key)
		{
			return $this->memcachedClient->delete($key);
		}

		public function drop($key)
		{
			return $this->free($key);
		}
	}
?>