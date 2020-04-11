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

namespace OnPHP\Core\Logic;

use OnPHP\Core\Base\StaticFactory;

/**
 * Extensive facilities for searching through label trees are provided.
 *
 * @see http://www.postgresql.org/docs/current/interactive/ltree.html
 * @ingroup Logic
**/
final class LTreeExpression extends StaticFactory
{
	const ANCESTOR 		= '@>';
	const DESCENDANT	= '<@';
	const MATCH			= '~';
	const SEARCH		= '@';

	/**
	 * Is left argument an ancestor of right (or equal)?
	 *
	 * @return BinaryExpression
	**/
	public static function ancestor($left, $right)
	{
		return new BinaryExpression($left, $right, self::ANCESTOR);
	}

	/**
	 * Is left argument a descendant of right (or equal)?
	 *
	 * @return BinaryExpression
	**/
	public static function descendant($left, $right)
	{
		return new BinaryExpression($left, $right, self::DESCENDANT);
	}

	/**
	 * @return BinaryExpression
	**/
	public static function match($ltree, $lquery)
	{
		return new BinaryExpression($ltree, $lquery, self::MATCH);
	}

	/**
	 * @return BinaryExpression
	**/
	public static function search($ltree, $ltxtquery)
	{
		return new BinaryExpression($ltree, $ltxtquery, self::SEARCH);
	}
}
?>