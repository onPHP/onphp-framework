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

namespace OnPHP\Main\OQL;

use OnPHP\Core\Base\StaticFactory;
use OnPHP\Main\OQL\Parser\OqlGroupByParser;
use OnPHP\Main\OQL\Parser\OqlHavingParser;
use OnPHP\Main\OQL\Parser\OqlOrderByParser;
use OnPHP\Main\OQL\Parser\OqlSelectParser;
use OnPHP\Main\OQL\Parser\OqlSelectPropertiesParser;
use OnPHP\Main\OQL\Parser\OqlWhereParser;
use OnPHP\Main\OQL\Statement\OqlHavingClause;
use OnPHP\Main\OQL\Statement\OqlOrderByClause;
use OnPHP\Main\OQL\Statement\OqlProjectionClause;
use OnPHP\Main\OQL\Statement\OqlSelectPropertiesClause;
use OnPHP\Main\OQL\Statement\OqlSelectQuery;
use OnPHP\Main\OQL\Statement\OqlWhereClause;

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
	 * @return OqlProjectionClause
	**/
	public static function groupBy($clause)
	{
		return OqlGroupByParser::create()->parse($clause);
	}

	/**
	 * @return OqlOrderByClause
	**/
	public static function orderBy($clause)
	{
		return OqlOrderByParser::create()->parse($clause);
	}

	/**
	 * @return OqlHavingClause
	**/
	public static function having($clause)
	{
		return OqlHavingParser::create()->parse($clause);
	}
}
?>