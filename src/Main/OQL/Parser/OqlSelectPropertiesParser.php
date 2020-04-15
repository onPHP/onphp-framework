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

use OnPHP\Main\Criteria\Projection\AverageNumberProjection;
use OnPHP\Main\Criteria\Projection\DistinctCountProjection;
use OnPHP\Main\Criteria\Projection\MaximalNumberProjection;
use OnPHP\Main\Criteria\Projection\MinimalNumberProjection;
use OnPHP\Main\Criteria\Projection\PropertyProjection;
use OnPHP\Main\Criteria\Projection\RowCountProjection;
use OnPHP\Main\Criteria\Projection\SumProjection;
use OnPHP\Main\OQL\Expression\OqlQueryParameter;
use OnPHP\Main\OQL\Statement\OqlSelectPropertiesClause;

final class OqlSelectPropertiesParser extends OqlParser
{
	// class map
	const SUM_PROJECTION			= 'sum';
	const AVG_PROJECTION			= 'avg';
	const MIN_PROJECTION			= 'min';
	const MAX_PROJECTION			= 'max';
	const COUNT_PROJECTION			= 'count';
	const DISTINCT_COUNT_PROJECTION	= 1;
	const PROPERTY_PROJECTION		= 2;

	private static $classMap = array(
		self::SUM_PROJECTION			=> SumProjection::class,
		self::AVG_PROJECTION			=> AverageNumberProjection::class,
		self::MIN_PROJECTION			=> MinimalNumberProjection::class,
		self::MAX_PROJECTION			=> MaximalNumberProjection::class,
		self::COUNT_PROJECTION			=> RowCountProjection::class,
		self::DISTINCT_COUNT_PROJECTION	=> DistinctCountProjection::class,
		self::PROPERTY_PROJECTION		=> PropertyProjection::class
	);

	/**
	 * @return OqlSelectPropertiesParser
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return OqlSelectPropertiesClause
	**/
	protected function makeOqlObject()
	{
		return OqlSelectPropertiesClause::create();
	}

	protected function handleState()
	{
		if ($this->state == self::INITIAL_STATE) {
			$list = $this->getCommaSeparatedList(
				array($this, 'getArgumentExpression'),
				'expecting expression or aggregate function call'
			);

			foreach ($list as $argument)
				$this->oqlObject->add($argument);
		}

		return self::FINAL_STATE;
	}

	/**
	 * @return OqlQueryParameter
	**/
	protected function getArgumentExpression()
	{
		$token = $this->tokenizer->peek();

		// aggregate function
		if ($this->checkToken($token, OqlToken::AGGREGATE_FUNCTION)) {
			$this->tokenizer->next();

			if ($this->openParentheses(false)) {

				if (($functionName = $this->getTokenValue($token)) == 'count') {
					if ($this->checkKeyword($this->tokenizer->peek(), 'distinct')) {
						$this->tokenizer->next();
						$functionName = self::DISTINCT_COUNT_PROJECTION;
					}

					$expression = $this->getLogicExpression();

				} else {
					$expression = $this->getArithmeticExpression();
				}

				$this->closeParentheses(true, "in function call: {$this->getTokenValue($token)}");

				return $this->makeQueryExpression(
					self::$classMap[$functionName],
					$expression,
					$this->getAlias()
				);

			} else
				$this->tokenizer->back();
		}

		// property
		if ($this->checkKeyword($token, 'distinct')) {
			$token = $this->tokenizer->next();
			$this->oqlObject->setDistinct(true);
		}

		return $this->makeQueryExpression(
			self::$classMap[self::PROPERTY_PROJECTION],
			$this->getLogicExpression(),
			$this->getAlias()
		);
	}

	/**
	 * @return OqlToken
	**/
	private function getAlias()
	{
		if ($this->checkKeyword($this->tokenizer->peek(), 'as')) {
			$this->tokenizer->next();

			if (
				!($alias = $this->tokenizer->next())
				|| !$this->checkIdentifier($alias)
			) {
				$this->error(
					'expecting alias name:',
					$this->getTokenValue($alias, true)
				);
			}

			return $alias;
		}

		return null;
	}
}
?>