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
	 * @see http://www.hibernate.org/hib_docs/v3/reference/en/html/querycriteria.html#querycriteria-projection
	 * 
	 * @ingroup Criteria
	**/
	namespace Onphp;

	final class Projection extends StaticFactory
	{
		/**
		 * @return \Onphp\SumProjection
		**/
		public static function sum($property, $alias = null)
		{
			return new SumProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\AverageNumberProjection
		**/
		public static function avg($property, $alias = null)
		{
			return new AverageNumberProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\MappableObjectProjection
		**/
		public static function mappable(MappableObject $object, $alias = null)
		{
			return new MappableObjectProjection($object, $alias);
		}
		
		/**
		 * @return \Onphp\MinimalNumberProjection
		**/
		public static function min($property, $alias = null)
		{
			return new MinimalNumberProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\MaximalNumberProjection
		**/
		public static function max($property, $alias = null)
		{
			return new MaximalNumberProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\PropertyProjection
		**/
		public static function property($property, $alias = null)
		{
			return new PropertyProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\RowCountProjection
		**/
		public static function count($property = null, $alias = null)
		{
			return new RowCountProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\DistinctCountProjection
		**/
		public static function distinctCount($property = null, $alias = null)
		{
			return new DistinctCountProjection($property, $alias);
		}
		
		/**
		 * @return \Onphp\ProjectionChain
		**/
		public static function chain()
		{
			return new ProjectionChain();
		}
		
		/**
		 * @return \Onphp\GroupByPropertyProjection
		**/
		public static function group($property)
		{
			return new GroupByPropertyProjection($property);
		}
		
		/**
		 * @return \Onphp\GroupByClassProjection
		**/
		public static function groupByClass($class)
		{
			return new GroupByClassProjection($class);
		}
		
		/**
		 * @return \Onphp\HavingProjection
		**/
		public static function having(LogicalObject $logic)
		{
			return new HavingProjection($logic);
		}
		
		/**
		 * @return \Onphp\ClassProjection
		**/
		public static function clazz($className)
		{
			return new ClassProjection($className);
		}
	}
?>