<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
	
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
		public static function union($left, $right)
		{
			return self::create($left, $right, LogicalExpression::UNION);
		}
		
		public static function unionBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::UNION);		
		}
		
		public static function unionAll($left, $right)
		{
			return self::create($left, $right, LogicalExpression::UNION_ALL);
		}
		
		public static function unionAllBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::UNION_ALL);
		}
		
		public static function intersect($left, $right)
		{
			return self::create($left, $right, LogicalExpression::INTERSECT);
		}
		
		public static function intersectBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::INTERSECT);
		}
		
		public static function intersectAll($left, $right)
		{
			return self::create($left, $right, LogicalExpression::INTERSECT_ALL);
		}
		
		public static function intersectAllBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::INTERSECT_ALL);
		}
		
		public static function except($left, $right)
		{
			return self::create($left, $right, LogicalExpression::EXCEPT);
		}
		
		public static function exceptBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::EXCEPT);
		}
	
		public static function exceptAll($left, $right)
		{
			return self::create($left, $right, LogicalExpression::EXCEPT_ALL);
		}
		
		public static function exceptAllBlock()
		{
			$args = func_get_args();
			return self::block($args, LogicalExpression::EXCEPT_ALL);
		}
		
		private static function block($args, $logic)
		{
			return new LogicalBlock($args, $logic);
		}
		
		/// public methods should be enough
		private static function create($left, $right, $logic)
		{
			return new LogicalExpression($left, $right, $logic);
		}
	}
?>