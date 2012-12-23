<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Criteria
	**/
	final class FetchStrategy extends Enumeration
	{
		const JOIN		= 1;
		const CASCADE	= 2;
		const LAZY		= 3;
		
		protected $names = array(
			self::JOIN		=> 'join',
			self::CASCADE	=> 'cascade',
			self::LAZY		=> 'lazy'
		);
		
		/**
		 * @return FetchStrategy
		**/
		public function setId($id)
		{
			Assert::isNull($this->id, 'i am immutable one!');
			
			return parent::setId($id);
		}
		
		/**
		 * @return FetchStrategy
		**/
		public static function join()
		{
			return self::getInstance(self::JOIN);
		}
		
		/**
		 * @return FetchStrategy
		**/
		public static function cascade()
		{
			return self::getInstance(self::CASCADE);
		}
		
		/**
		 * @return FetchStrategy
		**/
		public static function lazy()
		{
			return self::getInstance(self::LAZY);
		}
		
		/**
		 * @return FetchStrategy
		**/
		private static function getInstance($id)
		{
			static $instances = array();
			
			if (!isset($instances[$id]))
				$instances[$id] = new self($id);
			
			return $instances[$id];
		}
	}
