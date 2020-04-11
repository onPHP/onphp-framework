<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Main\OQL\Expression;

use OnPHP\Core\Logic\PrefixUnaryExpression;

/**
 * @ingroup OQL
**/
final class OqlPrefixMinusExpression extends OqlQueryExpression
{
	const CLASS_NAME = PrefixUnaryExpression::class;

	public function __construct(OqlQueryParameter $subject)
	{
		$this->
			setClassName(self::CLASS_NAME)->
			addParameter(
				OqlQueryParameter::create()->
					setValue(PrefixUnaryExpression::MINUS)
			)->
			addParameter($subject);
	}

	public function evaluate($values)
	{
		$value = $this->getParameter(1)->evaluate($values);

		if (is_numeric($value))
			return -$value;
		else
			return parent::evaluate($values);
	}
}
?>