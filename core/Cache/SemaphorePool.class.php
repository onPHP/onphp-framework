<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
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
	 * Single access point to application-wide locker implementation.
	 * 
	 * @see SystemFiveLocker for default locker
	 * @see FileLocker for 'universal' locker
	 * @see DirectoryLocker for slow and dirty locker
	 * 
	 * @ingroup Lockers
	**/
	final class SemaphorePool extends BaseLocker
	{
		private static $lockerName	= 'SystemFiveLocker';
		private static $locker		= null;
		
		protected function __construct()
		{
			self::$locker = Singleton::getInstance(self::$lockerName);
		}
		
		public static function setDefaultLocker($name)
		{
			self::$lockerName = $name;
			self::$locker = Singleton::getInstance($name);
		}
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function get($key)
		{
			return self::$locker->get($key);
		}
		
		public function free($key)
		{
			return self::$locker->free($key);
		}
		
		public function drop($key)
		{
			return self::$locker->drop($key);
		}
		
		public function clean()
		{
			return self::$locker->clean();
		}
		
		public function __destruct()
		{
			self::$locker->clean();
		}
	}
?>