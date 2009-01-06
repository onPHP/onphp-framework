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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OQL extends StaticFactory
	{
		/**
		 * @return OqlSelectQuery
		**/
		public static function select($query)
		{
			return OqlSelectParser::create()->parse($query);
		}
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public static function properties($clause)
		{
			return OqlSelectPropertiesParser::create()->parse($clause);
		}
		
		/**
		 * @return OqlWhereClause
		**/
		public static function where($clause)
		{
			return OqlWhereParser::create()->parse($clause);
		}
		
		/**
		 * @return OqlSelectProjectionClause
		**/
		public static function groupBy($clause)
		{
			return OqlSelectGroupByParser::create()->parse($clause);
		}
		
		/**
		 * @return OqlSelectOrderByClause
		**/
		public static function orderBy($clause)
		{
			return OqlSelectOrderByParser::create()->parse($clause);
		}
		
		/**
		 * @return OqlSelectHavingClause
		**/
		public static function having($clause)
		{
			return OqlSelectHavingParser::create()->parse($clause);
		}
	}
?>