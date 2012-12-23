<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class AutoloaderPool
	{
		private static $map = array();
		private static $recacheMap = array();
		
		public static function set($name, Autoloader $autoloader)
		{
			self::$map[$name] = $autoloader;
		}
		
		/**
		 * @param string $name
		 * @return Autoloader
		 */
		public static function get($name)
		{
			return isset(self::$map[$name]) ? self::$map[$name] : null;
		}
		
		public static function drop($name)
		{
			unset(self::$map[$name]);
		}
		
		public static function registerRecache(AutoloaderRecachable $autoloader)
		{
			self::$recacheMap[] = $autoloader;
		}
		
		public static function unregisterRecache(AutoloaderRecachable $autoloader)
		{
			foreach (self::$recacheMap as $key => $registeredAutoloader) {
				if ($registeredAutoloader == $autoloader)
					unset(self::$recacheMap[$key]);
			}
		}
		
		public static function autoloadWithRecache($className)
		{
			foreach (self::$recacheMap as $autoloader) {
				/* @var $autoloader AutoloaderRecachable */
				$autoloader->autoloadWithRecache($className);
				
				if (class_exists($className, false))
					return;
			}
		}
	}
