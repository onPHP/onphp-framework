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

use OnPHP\Core\OSQL\OrderBy;

/**
 * @ingroup OQL
**/
final class OqlOrderByExpression extends OqlQueryExpression
{
	const CLASS_NAME = OrderBy::class;

	private $direction = null;

	public function __construct(OqlQueryParameter $parameter, $direction)
	{
		$this->
			setClassName(self::CLASS_NAME)->
			addParameter($parameter);

		$this->direction = $direction;
	}

	/**
	 * @return OrderBy
	**/
	public function evaluate($values)
	{
		return parent::evaluate($values)->
			setDirection($this->direction);
	}
}
?>