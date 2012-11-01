<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	namespace Onphp;

	final class LogLevel extends Enumeration
	{
		const SEVERE	= 1; // highest value
		const WARNING	= 2;
		const INFO		= 3;
		const CONFIG	= 4;
		const FINE		= 5;
		const FINER		= 6;
		const FINEST	= 7; // lowest value
		
		protected $names = array(
			self::SEVERE	=> 'severe',
			self::WARNING	=> 'warning',
			self::INFO		=> 'info',
			self::CONFIG	=> 'config',
			self::FINE		=> 'fine',
			self::FINER		=> 'finer',
			self::FINEST	=> 'finest'
		);
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public function setId($id)
		{
			Assert::isNull($this->id, 'i am immutable one!');
			
			return parent::setId($id);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function severe()
		{
			return self::getInstance(self::SEVERE);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function warning()
		{
			return self::getInstance(self::WARNING);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function info()
		{
			return self::getInstance(self::INFO);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function config()
		{
			return self::getInstance(self::CONFIG);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function fine()
		{
			return self::getInstance(self::FINE);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function finer()
		{
			return self::getInstance(self::FINER);
		}
		
		/**
		 * @return \Onphp\LogLevel
		**/
		public static function finest()
		{
			return self::getInstance(self::FINEST);
		}
		
		/**
		 * @return \Onphp\LogLevel
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