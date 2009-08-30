<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	/**
	 * @ingroup Logic
	 * @see http://www.postgresql.org/docs/8.3/interactive/hstore.html
	**/
	final class HstoreExpression extends StaticFactory
	{
		const CONTAIN 		= '?';
		const GET_VALUE		= '->';
		const LEFT_CONTAIN	= '@>';
		const CONCAT		= '||';
		
		/**
		 * @return BinaryExpression
		**/
		public static function containKey($field, $key)
		{
			return new BinaryExpression($field, $key, self::CONTAIN);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function getValueByKey($field, $key)
		{
			return new BinaryExpression($field, $key, self::GET_VALUE);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function containValue($field, $key, $value)
		{
			return new BinaryExpression($field, "{$key}=>{$value}", self::LEFT_CONTAIN);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function concat($field, $value)
		{
			return new BinaryExpression($field, $value, self::CONCAT);
		}
	}
?>