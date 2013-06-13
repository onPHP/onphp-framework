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
	
	class AutoloaderClassNotFound implements Autoloader
	{
		private static $i = null;
		
		protected function __construct() {
			/* you must'n create it outside */
		}
		
		/**
		 * @return AutoloaderClassNotFound
		 */
		public static function me()
		{
			return self::$i
				?: (self::$i = new self());
		}
		
		public function autoload($className)
		{
			static $checkMethods = array(
				'class_exists',
				'interface_exists',
				'trait_exists',
			);
			
			AutoloaderPool::autoloadWithRecache($className);
			if (class_exists($className, false))
				return;

			foreach (debug_backtrace() as $call) {
				if (
					!empty($call['function'])
					&& empty($call['class'])
					&& in_array($call['function'], $checkMethods)
				)
					return;
			}
			
			throw new ClassNotFoundException('"'.$className.'"');
		}
		
		public function register()
		{
			$this->unregister();
			spl_autoload_register(array($this, 'autoload'));
		}
		
		public function unregister()
		{
			spl_autoload_unregister(array($this, 'autoload'));
		}
	}
?>