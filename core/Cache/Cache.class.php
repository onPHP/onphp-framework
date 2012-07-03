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
	
	/**
	 * System-wide access to selected CachePeer and DaoWorker.
	 * 
	 * @see CachePeer
	 * @see http://onphp.org/examples.Cache.en.html
	 * 
	 * @ingroup Cache
	 * 
	 * @example cacheSettings.php
	**/
	final class Cache extends StaticFactory implements Instantiatable
	{
		const NOT_FOUND			= 'nil';
		
		const EXPIRES_FOREVER	= 604800; // 7 days
		const EXPIRES_MAXIMUM	= 21600; // 6 hrs
		const EXPIRES_MEDIUM	= 3600; // 1 hr
		const EXPIRES_MINIMUM	= 300; // 5 mins
		
		const DO_NOT_CACHE		= -2005;
		
		/// map dao -> worker
		private static $map		= null;
		
		/// selected peer
		private static $peer	= null;
		
		/// default worker
		private static $worker	= null;
		
		/// spawned workers
		private static $instances = array();
		
		/**
		 * @return CachePeer
		**/
		public static function me()
		{
			if (!self::$peer || !self::$peer->isAlive())
				self::$peer = new RuntimeMemory();
			
			return self::$peer;
		}
		
		/* void */ public static function setPeer(CachePeer $peer)
		{
			self::$peer = $peer;
		}
		
		/**
		 * @return CachePeer 
		 */
		public static function getPeer()
		{
			return self::$peer;
		}
		
		/* void */ public static function setDefaultWorker($worker)
		{
			Assert::classExists($worker);
			
			self::$worker = $worker;
		}
		
		/**
		 * associative array, className -> workerName
		**/
		public static function setDaoMap($map)
		{
			self::$map = $map;
		}
		
		public static function appendDaoMap($map)
		{
			if (self::$map)
				self::$map = array_merge(self::$map, $map);
			else
				self::setDaoMap($map);
		}
		
		/**
		 * @return BaseDaoWorker
		**/
		public static function worker($dao)
		{
			$class = get_class($dao);
			
			if (!isset(self::$instances[$class])) {
				if (isset(self::$map[$class])) {
					$className = self::$map[$class];
					self::$instances[$class] = new $className($dao);
				} elseif ($worker = self::$worker)
					self::$instances[$class] = new $worker($dao);
				else
					self::$instances[$class] = new CommonDaoWorker($dao);
			}
			
			return self::$instances[$class];
		}
		
		/* void */ public static function dropWorkers()
		{
			self::$instances = array();
		}
	}
?>