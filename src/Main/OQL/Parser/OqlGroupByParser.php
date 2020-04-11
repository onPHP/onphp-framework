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

namespace OnPHP\Main\OQL\Parser;

use OnPHP\Main\Criteria\Projection\GroupByPropertyProjection;
use OnPHP\Main\OQL\Statement\OqlProjectionClause;

final class OqlGroupByParser extends OqlParser
{
	const CLASS_NAME = GroupByPropertyProjection::class;

	/**
	 * @return OqlGroupByParser
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return OqlProjectionClause
	**/
	protected function makeOqlObject()
	{
		return OqlProjectionClause::create();
	}

	protected function handleState()
	{
		if ($this->state == self::INITIAL_STATE) {
			$list = $this->getCommaSeparatedList(
				array($this, 'getLogicExpression'),
				"expecting expression in 'group by' clause"
			);

			foreach ($list as $argument) {
				$this->oqlObject->add(
					$this->makeQueryExpression(self::CLASS_NAME, $argument)
				);
			}
		}

		return self::FINAL_STATE;
	}
}
?>