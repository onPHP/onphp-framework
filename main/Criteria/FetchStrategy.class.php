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
		public static function join()
		{
			return new self(self::JOIN);
		}
		
		/**
		 * @return FetchStrategy
		**/
		public static function cascade()
		{
			return new self(self::CASCADE);
		}
		
		/**
		 * @return FetchStrategy
		**/
		public static function lazy()
		{
			return new self(self::LAZY);
		}
	}
?>