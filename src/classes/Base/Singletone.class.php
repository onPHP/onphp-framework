<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class SingletoneException extends BaseException
	{
		private $instance = null;
		
		public function __construct($instance = null)
		{
			$this->instance = $instance;
		}
		
		public function getInstance()
		{
			return $this->instance;
		}
	}
	
	abstract class Singletone
	{
		protected function __construct($class = null)
		{
			static $instances = array();
			
			if (isset($instances[$class]))
				throw new SingletoneException($instances[$class]);
			else
				$instances[$class] = $this;
		}
		
		public static function getInstance($class = null, $args = null)
		{
			if (null === $class)
				$class = 'SingletoneInstance';

            // for Singletone::getInstance('class_name', $arg1, ...) calling
            if (2 < func_num_args()) {
                $args = func_get_args();
                array_shift($args);
            }

			try {
				return new $class($class, $args);
			} catch (SingletoneException $e) {
				return $e->getInstance();
			}
		}
	}
	
	class SingletoneInstance extends Singletone
	{
		public function __call($class, $args)
		{
			return Singletone::getInstance($class, $args);
		}
	}
?>