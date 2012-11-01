<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OQL
	**/
	namespace Onphp;

	final class OQL extends StaticFactory
	{
		/**
		 * @return \Onphp\OqlSelectQuery
		**/
		public static function select($query)
		{
			return OqlSelectParser::create()->parse($query);
		}
		
		/**
		 * @return \Onphp\OqlSelectPropertiesClause
		**/
		public static function properties($clause)
		{
			return OqlSelectPropertiesParser::create()->parse($clause);
		}
		
		/**
		 * @return \Onphp\OqlWhereClause
		**/
		public static function where($clause)
		{
			return OqlWhereParser::create()->parse($clause);
		}
		
		/**
		 * @return \Onphp\OqlProjectionClause
		**/
		public static function groupBy($clause)
		{
			return OqlGroupByParser::create()->parse($clause);
		}
		
		/**
		 * @return \Onphp\OqlOrderByClause
		**/
		public static function orderBy($clause)
		{
			return OqlOrderByParser::create()->parse($clause);
		}
		
		/**
		 * @return \Onphp\OqlHavingClause
		**/
		public static function having($clause)
		{
			return OqlHavingParser::create()->parse($clause);
		}
	}
?>