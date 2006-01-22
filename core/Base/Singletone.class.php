<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Inheritable Singletone's pattern implementation.
	 * 
	 * @ingroup Base
	 * 
	 * @example singletone.php
	**/
	abstract class Singletone
	{
		protected function __construct() {/* you can't create me */}
		
		final public static function getInstance(
			$class = null, $args = null /* , ... */
		)
		{
			static $instances = array();
			
			if (null == $class) {
				static $wrapper = null;
				
				if (null == $wrapper)
					$wrapper = new SingletoneInstance();
				
				return $wrapper;
			}
			
			// for Singletone::getInstance('class_name', $arg1, ...) calling
			if (2 < func_num_args()) {
				$args = func_get_args();
				array_shift($args);
			}

			if (!isset($instances[$class]))
				return $instances[$class] = new $class($class, $args);
			else
				return $instances[$class];
		}
		
		final private function __clone() {/* do not clone me */}
	}
	
	/**
	 * @ingroup Base
	**/
	final class SingletoneInstance extends Singletone
	{
		public function __call($class, $args = null)
		{
			return Singletone::getInstance($class, $args);
		}
	}
?>