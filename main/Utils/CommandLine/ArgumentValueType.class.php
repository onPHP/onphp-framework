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

	final class ArgumentValueType extends Enumeration implements Stringable
	{
		const REQUIRED		= 0x1;
		const OPTIONAL		= 0x2;
		const NO_VALUE		= 0x3;
		
		protected $names = array(
			self::REQUIRED	=> 'Обязательный',
			self::OPTIONAL	=> 'Опциональный',
			self::NO_VALUE	=> 'Без значения'
		);
		
		private $getOptValue = array(
			self::REQUIRED	=> ':',
			self::OPTIONAL	=> '::',
			self::NO_VALUE	=> null
		);
		
		public function toString()
		{
			return $this->getOptValue[$this->id];
		}
		
		/**
		 * @return ArgumentValueType
		**/
		public static function required()
		{
			return self::getInstance(self::REQUIRED);
		}
		
		/**
		 * @return ArgumentValueType
		**/
		public static function optional()
		{
			return self::getInstance(self::OPTIONAL);
		}
		
		/**
		 * @return ArgumentValueType
		**/
		public static function noValue()
		{
			return self::getInstance(self::NO_VALUE);
		}
		
		/**
		 * @return ArgumentValueType
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