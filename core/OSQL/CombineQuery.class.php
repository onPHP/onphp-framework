<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
	 * @see PostgreSQL Documentation, Chapter Combining Queries 
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
			return new QueryCombination($left, $right, CombineQuery::UNION);
		}	
		
		/**
		 * @return QueryChain
		**/
		public static function unionBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::UNION);		
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function unionAll($left, $right)
		{
			return new QueryCombination($left, $right, CombineQuery::UNION_ALL);
		}	
		
		/**
		 * @return QueryChain
		**/
		public static function unionAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::UNION_ALL);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function intersect($left, $right)
		{
			return new QueryCombination($left, $right, CombineQuery::INTERSECT);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function intersectBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::INTERSECT);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function intersectAll($left, $right)
		{
			return new QueryCombination($left, $right, CombineQuery::INTERSECT_ALL);
		}	
		
		/**
		 * @return QueryChain
		**/
		public static function intersectAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::INTERSECT_ALL);
		}
		
		/**
		 * @return QueryCombination
		**/
		public static function except($left, $right)
		{
			return new QueryCombination($left, $right, CombineQuery::EXCEPT);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function exceptBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::EXCEPT);
		}
	
		/**
		 * @return QueryCombination
		**/
		public static function exceptAll($left, $right)
		{
			return new QueryCombination($left, $right, CombineQuery::EXCEPT_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public static function exceptAllBlock()
		{
			$args = func_get_args();
			
			return QueryChain::block($args, CombineQuery::EXCEPT_ALL);
		}
	}
?>