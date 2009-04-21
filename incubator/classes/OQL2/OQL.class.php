<?php
/***************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                             *
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
	final class OQL extends StaticFactory
	{
		/**
		 * @return OqlSelectQuery
		**/
		public static function select($string)
		{
			return self::parse(
				OqlGrammar::SELECT,
				$string,
				OqlSelectQuery::create()
			);
		}
		
		/**
		 * @return OqlProjectionClause
		**/
		public static function properties($string)
		{
			return self::parse(
				OqlGrammar::PROPERTIES,
				$string,
				OqlProjectionClause::create()
			);
		}
		
		/**
		 * @return OqlExpressionClause
		**/
		public static function where($string)
		{
			return self::parse(
				OqlGrammar::WHERE,
				$string,
				OqlExpressionClause::create()
			);
		}
		
		/**
		 * @return OqlProjectionClause
		**/
		public static function groupBy($string)
		{
			return self::parse(
				OqlGrammar::GROUP_BY,
				$string,
				OqlProjectionClause::create()
			);
		}
		
		/**
		 * @return OqlOrderClause
		**/
		public static function orderBy($string)
		{
			return self::parse(
				OqlGrammar::ORDER_BY,
				$string,
				OqlOrderClause::create()
			);
		}
		
		/**
		 * @return OqlProjectionClause
		**/
		public static function having($string)
		{
			return self::parse(
				OqlGrammar::HAVING,
				$string,
				OqlProjectionClause::create()
			);
		}
		
		private static function parse($ruleId, $string, OqlBindableNodeWrapper $node)
		{
			return OqlParser::create()->
				setGrammar(OqlGrammar::me())->
				setRuleId($ruleId)->
				parse($string, $node);
		}
	}
?>