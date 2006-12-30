<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see http://www.hibernate.org/hib_docs/v3/reference/en/html/querycriteria.html#querycriteria-projection
	 * 
	 * @ingroup Criteria
	**/
	final class Projection extends StaticFactory
	{
		/**
		 * @return AverageNumberProjection
		**/
		public static function avg($property, $alias = null)
		{
			return new AverageNumberProjection($property, $alias);
		}
		
		/**
		 * @return MinimalNumberProjection
		**/
		public static function min($property, $alias = null)
		{
			return new MinimalNumberProjection($property, $alias);
		}
		
		/**
		 * @return MaximalNumberProjection
		**/
		public static function max($property, $alias = null)
		{
			return new MaximalNumberProjection($property, $alias);
		}
		
		/**
		 * @return PropertyProjection
		**/
		public static function property($property, $alias = null)
		{
			return new PropertyProjection($property, $alias);
		}
		
		/**
		 * @return RowCountProjection
		**/
		public static function count($property = null, $alias = null)
		{
			return new RowCountProjection($property, $alias);
		}
		
		/**
		 * @return ProjectionChain
		**/
		public static function chain()
		{
			return new ProjectionChain();
		}
		
		/**
		 * @return GroupByPropertyProjection
		**/
		public static function group($property)
		{
			return new GroupByPropertyProjection($property);
		}
	}
?>