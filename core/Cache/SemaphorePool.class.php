<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class SemaphorePool extends StaticFactory
	{
		private static $pool = array();
		
		public static function me()
		{
			static $instance = null;
			
			if (!$instance)
				$instance = new SemaphorePool();
			
			return $instance;
		}
		
		public function get($key)
		{
			try {
				if (!isset(self::$pool[$key]))
					self::$pool[$key] = sem_get($key, 1, 0600, false);
				
				sem_acquire(self::$pool[$key]);
				
				return self::$pool[$key];
			} catch (BaseException $e) {
				return null;
			}
			
			/* NOTREACHED */
		}
		
		public function free($key)
		{
			if (isset(self::$pool[$key])) {
				try {
					return sem_release(self::$pool[$key]);
				} catch (BaseException $e) {
					// acquired by another process
					return false;
				}
			}
			
			return null;
		}
		
		public function drop($key)
		{
			if (isset(self::$pool[$key])) {
				try {
					return sem_remove(self::$pool[$key]);
				} catch (BaseException $e) {
					unset(self::$pool[$key]); // already race-removed
					return false;
				}
			}
			
			return null;
		}
		
		public function __destruct()
		{
			foreach (self::$pool as $key => $sem) {
				try {
					sem_remove($sem);
				} catch (BaseException $e) {
					// we have to be silent
				}
			}
		}
	}
?>