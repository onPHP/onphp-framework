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

	final class ArgumentType extends Enumeration
	{
		const SHORT		= 0x1;
		const LONG		= 0x2;
		
		protected $names = array(
			self::SHORT	=> 'Короткий',
			self::LONG	=> 'Длинный'
		);
		
		/**
		 * @return ArgumentType
		**/
		public static function short()
		{
			return self::getInstance(self::SHORT);
		}
		
		/**
		 * @return ArgumentType
		**/
		public static function long()
		{
			return self::getInstance(self::LONG);
		}
		
		/**
		 * @return ArgumentType
		**/
		private static function getInstance($id)
		{
			static $instances = array();
			
			if (!isset($instances[$id]))
				$instances[$id] = new self($id);
			
			return $instances[$id];
		}
	}
?>