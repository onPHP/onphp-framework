<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	/**
	 * The results of queries can be combined using the set
	 * operations union, intersection, and difference.
	 * 
	 * query1 UNION [ALL] query2 ....
	 * query1 INTERSECT [ALL] query2 ....
	 * query1 EXCEPT [ALL] query2 ....
	 * 
	 * @see http://www.postgresql.org/docs/current/interactive/queries-union.html
	 * 
	 * @ingroup OSQL
	**/
	final class CombineQuery extends StaticFactory
	{
		const UNION				= 'UNION';
		const UNION_ALL			= 'UNION ALL';
		
		const INTERSECT			= 'INTERSECT';
		const INTERSECT_ALL		= 'INTERSECT ALL';
		
		const EXCEPT			= 'EXCEPT';
		const EXCEPT_ALL		= 'EXCEPT ALL';
		
		/**
		 * @return QueryCombination
		**/
		public static function union($left, $right)
		{
			return new QueryCombination($left, $right, self::UNION);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function unionBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::UNION);		
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function unionAll($left, $right)
		{
			return new QueryCombination($left, $right, self::UNION_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function unionAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::UNION_ALL);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function intersect($left, $right)
		{
			return new QueryCombination($left, $right, self::INTERSECT);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function intersectBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::INTERSECT);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function intersectAll($left, $right)
		{
			return new QueryCombination($left, $right, self::INTERSECT_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function intersectAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::INTERSECT_ALL);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function except($left, $right)
		{
			return new QueryCombination($left, $right, self::EXCEPT);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function exceptBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::EXCEPT);
		}
	
		/**
		 * @return QueryCombination
		**/
		public static function exceptAll($left, $right)
		{
			return new QueryCombination($left, $right, self::EXCEPT_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function exceptAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, self::EXCEPT_ALL);
		}
	}
?>