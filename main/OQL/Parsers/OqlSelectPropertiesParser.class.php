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

	namespace Onphp;

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
			self::SUM_PROJECTION			=> '\Onphp\SumProjection',
			self::AVG_PROJECTION			=> '\Onphp\AverageNumberProjection',
			self::MIN_PROJECTION			=> '\Onphp\MinimalNumberProjection',
			self::MAX_PROJECTION			=> '\Onphp\MaximalNumberProjection',
			self::COUNT_PROJECTION			=> '\Onphp\RowCountProjection',
			self::DISTINCT_COUNT_PROJECTION	=> '\Onphp\DistinctCountProjection',
			self::PROPERTY_PROJECTION		=> '\Onphp\PropertyProjection'
		);
		
		/**
		 * @return \Onphp\OqlSelectPropertiesParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\OqlSelectPropertiesClause
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
		 * @return \Onphp\OqlQueryParameter
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
		 * @return \Onphp\OqlToken
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