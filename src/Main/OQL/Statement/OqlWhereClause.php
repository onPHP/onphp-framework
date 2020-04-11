<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Main\OQL\Statement;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Logic\LogicalObject;
use OnPHP\Main\OQL\Expression\OqlInExpression;
use OnPHP\Main\OQL\Expression\OqlQueryExpression;

/**
 * @ingroup OQL
**/
final class OqlWhereClause extends OqlQueryExpressionClause
{
	/**
	 * @return OqlWhereClause
	**/
	public static function create()
	{
		return new self;
	}

	protected static function checkExpression(OqlQueryExpression $expression)
	{
		if (!$expression instanceof OqlInExpression)
			Assert::isInstance($expression->getClassName(), LogicalObject::class);
	}
}
?>