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
/* $Id$ */

	/**
	 * Parses OQL select query.
	 * 
	 * Examples:
	 * 
	 * from User where id = $1
	 * count(id) as count, count(distinct Name) as distinctCount from User
	 * (id + -$1) / 2 as idExpression, distinct id from User
	 * where (Name not ilike 'user%') and id <= 10 and created between $2 and $3
	 * order by id desc, Name asc
	 * limit 10 offset $2
	 * from User having $1 > 0 group by id
	 * 
	 * @see OQL::select
	 * @see http://www.hibernate.org/hib_docs/reference/en/html/queryhql.html
	 * @see doc/OQL-BNF
	 * 
	 * @ingroup OQL
	**/
	final class OqlSelectParser extends OqlParser
	{
		// states
		const PROPERTY_STATE	= 1;
		const FROM_STATE		= 2;
		const WHERE_STATE		= 3;
		const GROUP_BY_STATE	= 4;
		const ORDER_BY_STATE	= 5;
		const HAVING_STATE		= 6;
		const LIMIT_STATE		= 7;
		const OFFSET_STATE		= 8;
		
		// contexts for comma separated lists
		const PROPERTY_CONTEXT	= 1;
		const IN_CONTEXT		= 2;
		const GROUP_BY_CONTEXT	= 3;
		const ORDER_BY_CONTEXT	= 4;
		
		// class map
		const SUM_PROJECTION			= 'sum';
		const AVG_PROJECTION			= 'avg';
		const MIN_PROJECTION			= 'min';
		const MAX_PROJECTION			= 'max';
		const COUNT_PROJECTION			= 'count';
		const DISTINCT_COUNT_PROJECTION	= 1;
		const PROPERTY_PROJECTION		= 2;
		const GROUP_BY_PROJECTION		= 3;
		const HAVING_PROJECTION			= 4;
		const PREFIX_UNARY_EXPRESSION	= 5;
		const POSTFIX_UNARY_EXPRESSION	= 6;
		const BINARY_EXPRESSION			= 7;
		const BETWEEN_EXPRESSION		= 8;
		
		private static $classMap = array(
			self::SUM_PROJECTION			=> 'SumProjection',
			self::AVG_PROJECTION			=> 'AverageNumberProjection',
			self::MIN_PROJECTION			=> 'MinimalNumberProjection',
			self::MAX_PROJECTION			=> 'MaximalNumberProjection',
			self::COUNT_PROJECTION			=> 'RowCountProjection',
			self::DISTINCT_COUNT_PROJECTION	=> 'DistinctCountProjection',
			self::PROPERTY_PROJECTION		=> 'PropertyProjection',
			self::GROUP_BY_PROJECTION		=> 'GroupByPropertyProjection',
			self::HAVING_PROJECTION			=> 'HavingProjection',
			self::PREFIX_UNARY_EXPRESSION	=> 'PrefixUnaryExpression',
			self::POSTFIX_UNARY_EXPRESSION	=> 'PostfixUnaryExpression',
			self::BINARY_EXPRESSION			=> 'BinaryExpression',
			self::BETWEEN_EXPRESSION		=> 'LogicalBetween'
		);
		
		// binary operator map		
		private static $binaryOperatorMap = array(
			'='					=> BinaryExpression::EQUALS,
			'!='				=> BinaryExpression::NOT_EQUALS,
			'and'				=> BinaryExpression::EXPRESSION_AND,
			'or'				=> BinaryExpression::EXPRESSION_OR,
			'>'					=> BinaryExpression::GREATER_THAN,
			'>='				=> BinaryExpression::GREATER_OR_EQUALS,
			'<'					=> BinaryExpression::LOWER_THAN,
			'<='				=> BinaryExpression::LOWER_OR_EQUALS,
			'like'				=> BinaryExpression::LIKE,
			'not like'			=> BinaryExpression::NOT_LIKE,
			'ilike'				=> BinaryExpression::ILIKE,
			'not ilike'			=> BinaryExpression::NOT_ILIKE,
			'similar to'		=> BinaryExpression::SIMILAR_TO,
			'not similar to'	=> BinaryExpression::NOT_SIMILAR_TO,
			'+'					=> BinaryExpression::ADD,
			'-'					=> BinaryExpression::SUBSTRACT,
			'*'					=> BinaryExpression::MULTIPLY,
			'/'					=> BinaryExpression::DIVIDE
		);
		
		// boolean operators priority
		const LOGIC_PRIORITY_OR			= 1;
		const LOGIC_PRIORITY_AND		= 2;
		const LOGIC_PRIORITY_LT_GT		= 3;
		const LOGIC_PRIORITY_EQ			= 4;
		const LOGIC_PRIORITY_TERMINAL	= 5;
		
		const LOGIC_PRIORITY_LOWEST		= self::LOGIC_PRIORITY_OR;
		const LOGIC_PRIORITY_UNARY_NOT	= self::LOGIC_PRIORITY_LT_GT;
		
		private static $logicPriorityMap = array(
			self::LOGIC_PRIORITY_OR			=> 'or',
			self::LOGIC_PRIORITY_AND		=> 'and',
			self::LOGIC_PRIORITY_LT_GT		=> array('>', '<', '>=', '<='),
			self::LOGIC_PRIORITY_EQ			=> array('=', '!='),
			self::LOGIC_PRIORITY_TERMINAL	=> null
		);
		
		// arithmetic operators priority
		const ARITHMETIC_PRIORITY_ADD		= 1;
		const ARITHMETIC_PRIORITY_MUL		= 2;
		const ARITHMETIC_PRIORITY_TERMINAL	= 3;
		
		const ARITHMETIC_PRIORITY_LOWEST	= self::ARITHMETIC_PRIORITY_ADD;
		
		private static $arithmeticPriorityMap = array(
			self::ARITHMETIC_PRIORITY_ADD		=> array('+', '-'),
			self::ARITHMETIC_PRIORITY_MUL		=> array('*', '/'),
			self::ARITHMETIC_PRIORITY_TERMINAL	=> null
		);
		
		/**
		 * @return OqlSelectParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		protected function makeQuery()
		{
			return OqlSelectQuery::create();
		}
		
		protected function handleState()
		{
			switch ($this->state) {
				case self::INITIAL_STATE:
				case self::PROPERTY_STATE:
					return $this->propertyState();
					
				case self::FROM_STATE:
					return $this->fromState();
					
				case self::WHERE_STATE:
					return $this->whereState();
					
				case self::GROUP_BY_STATE:
					return $this->groupByState();
					
				case self::ORDER_BY_STATE:
					return $this->orderByState();
					
				case self::HAVING_STATE:
					return $this->havingState();
					
				case self::LIMIT_STATE:
					return $this->limitState();
					
				case self::OFFSET_STATE:
					return $this->offsetState();
			}
			
			throw new WrongStateException('state machine is broken');
		}
		
		private function propertyState()
		{
			$token = $this->tokenizer->peek();
			
			if (!$token)
				$this->error("expecting 'from' clause");
			
			if ($this->checkKeyword($token, 'from'))
				return self::FROM_STATE;
			
			$list = $this->getCommaSeparatedList(
				self::PROPERTY_CONTEXT,
				'expecting expression or aggregate function call'
			);
			
			foreach ($list as $argument)
				$this->query->addProjection($argument);
			
			return self::FROM_STATE;
		}
		
		private function fromState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'from')) {
				$this->tokenizer->next();
				
				$class = $this->tokenizer->next();
				$className = $this->getTokenValue($class, true);
				
				if (
					!$this->checkIdentifier($class)
					|| !ClassUtils::isClassName($className)
				)
					$this->error("invalid class name: {$className}");
				
				if (!class_exists($className, true))
					$this->error("class does not exists: {$className}");
				
				if (!ClassUtils::isInstanceOf($className, 'DAOConnected'))
					$this->error("class must implement DAOConnected interface: {$className}");
				
				$this->query->setDao(
					call_user_func(array($className, 'dao'))
				);
				
			} else
				$this->error("expecting 'from' clause");
			
			return self::WHERE_STATE;
		}
		
		private function whereState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'where')) {
				$this->tokenizer->next();
				
				$argument = $this->getLogicExpression();
				if ($argument instanceof OqlQueryExpression)
					$this->query->setWhereExpression($argument);
				else
					$this->error("expecting 'where' expression");
				
				$this->checkParentheses("in 'where' expression");
			}
			
			return self::GROUP_BY_STATE;
		}
		
		private function groupByState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'group by')) {
				$this->tokenizer->next();
				
				$list = $this->getCommaSeparatedList(
					self::GROUP_BY_CONTEXT,
					"expecting identifier in 'group by' expression"
				);
				
				foreach ($list as $argument)
					$this->query->addProjection(
						$this->makeQueryExpression(
							self::$classMap[self::GROUP_BY_PROJECTION],
							$argument
						)
					);
			}
			
			return self::ORDER_BY_STATE;
		}
		
		private function orderByState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'order by')) {
				$this->tokenizer->next();
				
				$list = $this->getCommaSeparatedList(
					self::ORDER_BY_CONTEXT,
					"expecting expression in 'order by'"
				);
				
				foreach ($list as $argument)
					$this->query->addOrder($argument);
			}
			
			return self::HAVING_STATE;
		}
		
		private function havingState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'having')) {
				$this->tokenizer->next();
				
				if ($argument = $this->getLogicExpression())
					$this->query->addProjection(
						$this->makeQueryExpression(
							self::$classMap[self::HAVING_PROJECTION],
							$argument
						)
					);
				else
					$this->error("expecting 'having' expression");
				
				$this->checkParentheses("in 'having' expression");
			}
			
			return self::LIMIT_STATE;
		}
		
		private function limitState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'limit')) {
				$this->tokenizer->next();
				
				$token = $this->tokenizer->next();
				if (
					$this->checkToken($token, OqlToken::NUMBER)
					|| $this->checkToken($token, OqlToken::SUBSTITUTION)
				)
					$this->query->setLimit(
						$this->makeQueryParameter($token)
					);
					
				else
					$this->error("expecting 'limit' expression");
			}
			
			return self::OFFSET_STATE;
		}
		
		private function offsetState()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'offset')) {
				$this->tokenizer->next();
				
				$token = $this->tokenizer->next();
				if (
					$this->checkToken($token, OqlToken::NUMBER)
					|| $this->checkToken($token, OqlToken::SUBSTITUTION)
				)
					$this->query->setOffset(
						$this->makeQueryParameter($token)
					);
					
				else
					$this->error("expecting 'offset' expression");
			}
			
			return self::FINAL_STATE;
		}
		
		/**
		 * @return OqlToken
		**/
		private function getAlias()
		{
			if ($this->checkKeyword($this->tokenizer->peek(), 'as')) {
				$this->tokenizer->next();
				
				$alias = $this->tokenizer->next();
				if (!$this->checkIdentifier($alias))
					$this->error(
						"expecting alias name: {$this->getTokenValue($alias, true)}"
					);
				
				return $alias;
			}
			
			return null;
		}
		
		/**
		 * @return OqlQueryExpression
		**/
		private function getLogicExpression(
			$priority = self::LOGIC_PRIORITY_LOWEST
		)
		{
			$expression = null;
			
			// terminal boolean expressions
			if ($priority == self::LOGIC_PRIORITY_TERMINAL) {
				$token = $this->tokenizer->peek();
				if (!$token)
					return null;
				
				// arithmetic expression
				if ($this->isArithmeticExpression())
					return $this->getArithmeticExpression();
				
				// parentheses
				if ($this->openParentheses(false)) {
					$expression = $this->getLogicExpression();
					$this->closeParentheses(true, 'in expression');
					
					return $expression;
				}
				
				// prefix unary 'not'
				if ($this->checkKeyword($token, 'not')) {
					$this->tokenizer->next();
					
					if (
						$argument = $this->getLogicExpression(self::LOGIC_PRIORITY_UNARY_NOT)
					)
						return $this->makeQueryExpression(
							self::$classMap[self::PREFIX_UNARY_EXPRESSION],
							PrefixUnaryExpression::NOT,
							$argument
						);
						
					else
						$this->error('expecting argument in expression: not');
				}
				
				// first argument
				if (
					!($expression = $this->getIdentifierExpression())
					&& !($expression = $this->getConstantExpression())
				)
					$this->error(
						'expecting first argument in expression: '
						.$this->getTokenValue($this->tokenizer->peek(), true)
					);
				
				// not (like|ilike|between|similar to|in)
				$operator = $this->tokenizer->peek();
				if ($this->checkKeyword($operator, 'not')) {
					$this->tokenizer->next();
					$operator = $this->tokenizer->peek();
					$isNot = true;
				} else
					$isNot = false;
				
				// is ([not] null|true|false)
				if (
					!$isNot
					&& $this->checkKeyword($operator, 'is')
				) {
					$this->tokenizer->next();
					
					$logic = null;
					
					if ($this->checkKeyword($this->tokenizer->peek(), 'not')) {
						$this->tokenizer->next();
						$isNot = true;
					} else
						$isNot = false;
					
					if ($this->checkToken($this->tokenizer->peek(), OqlToken::NULL)) {
						$this->tokenizer->next();
						$logic = $isNot
							? PostfixUnaryExpression::IS_NOT_NULL
							: PostfixUnaryExpression::IS_NULL;
						
					} elseif (
						!$isNot
						&& $this->checkToken($this->tokenizer->peek(), OqlToken::BOOLEAN)
					) {
						$logic = $this->tokenizer->next()->getValue() === true
							? PostfixUnaryExpression::IS_TRUE
							: PostfixUnaryExpression::IS_FALSE;
					}
					
					if ($logic)
						return $this->makeQueryExpression(
							self::$classMap[self::POSTFIX_UNARY_EXPRESSION],
							$expression,
							$logic
						);
					
					else
						$this->error("expecting 'null', 'not null', 'true' or 'false'");
				
				// [not] in
				} elseif ($this->checkKeyword($operator, 'in')) {
					$isNotString = ($isNot ? 'not ' : '');
					$this->tokenizer->next();
					
					$this->openParentheses(true, 'in expression: '.$isNotString.'in');
					
					$list = $this->getCommaSeparatedList(
						self::IN_CONTEXT,
						'expecting constant or substitution in expression: '
						.$isNotString.'in'
					);
					
					if (is_array($list) && count($list) == 1)
						$list = reset($list);
					
					$this->closeParentheses(true, 'in expression: '.$isNotString.'in');
					
					return new OqlInExpression(
						$expression,
						$this->makeQueryParameter($list),
						$isNot ? InExpression::NOT_IN : InExpression::IN
					);
					
				// [not] (like|ilike|similar to)
				} elseif (
					$this->checkKeyword($operator, array('like', 'ilike', 'similar to'))
				) {
					$this->tokenizer->next();
					
					$isNotString = ($isNot ? 'not ' : '');
					
					$argument = $this->tokenizer->next();
					if (
						$this->checkToken($argument, OqlToken::STRING)
						|| $this->checkToken($argument, OqlToken::SUBSTITUTION)
					)
						return $this->makeQueryExpression(
							self::$classMap[self::BINARY_EXPRESSION],
							$expression,
							$argument,
							self::$binaryOperatorMap[
								$isNotString
								.$this->getTokenValue($operator)
							]
						);
						
					else
						$this->error(
							'expecting string constant or substitution: '
							.$isNotString
							.$this->getTokenValue($operator, true)
						);
				
				// between
				} elseif (
					!$isNot
					&& $this->checkKeyword($operator, 'between')
				) {
					$this->tokenizer->next();
					
					if (
						($argument1 = $this->getIdentifierExpression())
						|| ($argument1 = $this->getConstantExpression())
					) {
						if ($this->checkKeyword($this->tokenizer->next(), 'and')) {
							if (
								($argument2 = $this->getIdentifierExpression())
								|| ($argument2 = $this->getConstantExpression())
							)
								return $this->makeQueryExpression(
									self::$classMap[self::BETWEEN_EXPRESSION],
									$expression,
									$argument1,
									$argument2
								);
								
							else
								$this->error(
									'expecting second argument in expression: between'
								);
							
						} else
							$this->error(
								"expecting 'and' in expression: between"
							);
						
					} else
						$this->error(
							'expecting first argument in expression: between'
						);
				}
				
				if ($isNot)
					$this->error('expecting in, like, ilike or similar to');
				
			// and|or|comparison expression chain
			} else {
				$operatorList = self::$logicPriorityMap[$priority];
				$higherPriority = $priority + 1;
				
				if (!($expression = $this->getLogicExpression($higherPriority)))
					$this->error(
						'expecting first argument in expression: '
						.(
							is_array($operatorList)
								? implode('|', $operatorList)
								: $operatorList
						)
					);
				
				$tokenType =
					$priority == self::LOGIC_PRIORITY_OR
					|| $priority == self::LOGIC_PRIORITY_AND
						? OqlToken::KEYWORD
						: OqlToken::COMPARISON_OPERATOR;
				
				while (
					$this->checkToken(
						$this->tokenizer->peek(),
						$tokenType,
						$operatorList
					)
				) {
					$operator = $this->tokenizer->next();
					
					if ($expression2 = $this->getLogicExpression($higherPriority))
						$expression = $this->makeQueryExpression(
							self::$classMap[self::BINARY_EXPRESSION],
							$expression,
							$expression2,
							self::$binaryOperatorMap[$operator->getValue()]
						);
						
					else
						$this->error(
							'expecting second argument in expression: '
							.$this->getTokenValue($operator, true)
						);
				}
			}
			
			return $expression;
		}
		
		/**
		 * @return OqlQueryExpression
		**/
		private function getArithmeticExpression(
			$priority = self::ARITHMETIC_PRIORITY_LOWEST
		)
		{
			// terminal arithmetic expressions
			if ($priority == self::ARITHMETIC_PRIORITY_TERMINAL) {
				$token = $this->tokenizer->peek();
				if (!$token)
					return null;
				
				// unary minus
				if ($isUnaryMinus = $this->checkUnaryMinus($token))
					$this->tokenizer->next();
				
				// parentheses
				if ($this->openParentheses(false)) {
					$expression = $this->getArithmeticExpression();
					$this->closeParentheses(true, 'in expression');
					
				// argument
				} elseif ($expression = $this->getArithmeticArgumentExpression()) {
					// $expression
					
				} else
					$this->error(
						'expecting argument in expression: '
						.$this->getTokenValue($this->tokenizer->peek(), true)
					);
				
				$expression = $this->makeQuerySignedExpression($expression, $isUnaryMinus);
				
			// +|-|*|/ expression chain
			} else {
				$operatorList = self::$arithmeticPriorityMap[$priority];
				$higherPriority = $priority + 1;
				
				if (!($expression = $this->getArithmeticExpression($higherPriority)))
					$this->error(
						'expecting first argument in expression: '
						.implode('|', $operatorList)
					);
				
				while (
					$this->checkToken(
						$this->tokenizer->peek(),
						OqlToken::ARITHMETIC_OPERATOR,
						$operatorList
					)
				) {
					$operator = $this->tokenizer->next();
					
					if ($expression2 = $this->getArithmeticExpression($higherPriority))
						$expression = $this->makeQueryExpression(
							self::$classMap[self::BINARY_EXPRESSION],
							$expression,
							$expression2,
							self::$binaryOperatorMap[$operator->getValue()]
						);
					else
						$this->error(
							'expecting second argument in expression: '
							.$this->getTokenValue($operator, true)
						);
				}
			}
			
			return $expression;
		}
		
		private function isArithmeticExpression()
		{
			$index = $this->tokenizer->getIndex();
			
			// skip open parentheses
			while (
				$this->checkToken($this->tokenizer->peek(), OqlToken::PARENTHESES, '(')
			)
				$this->tokenizer->next();
				
			// skip unary minus
			if ($this->checkUnaryMinus($this->tokenizer->peek()))
				$this->tokenizer->next();
			
			$result =
				$this->getArithmeticArgumentExpression()
				&& $this->checkToken($this->tokenizer->peek(), OqlToken::ARITHMETIC_OPERATOR);
			
			$this->tokenizer->setIndex($index);
			
			return $result;
		}
		
		/**
		 * @throws SyntaxErrorException
		 * @throws WrongArgumentException
		 * @return OqlQueryParameter
		**/
		protected function getArgumentExpression($context, $message)
		{
			switch ($context) {
				
				case self::PROPERTY_CONTEXT:
					
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
								
							} else
								$expression = $this->getArithmeticExpression();
							
							$this->closeParentheses(true, "in function call: {$this->getTokenValue($token)}");
							
							$argument = $this->makeQueryExpression(
								self::$classMap[$functionName],
								$expression,
								$this->getAlias()
							);
							
							break;
							
						} else
							$this->tokenizer->back();
					}
					
					// property
					if ($this->checkKeyword($token, 'distinct')) {
						$token = $this->tokenizer->next();
						$this->query->setDistinct(true);
					}
					
					$argument = $this->makeQueryExpression(
						self::$classMap[self::PROPERTY_PROJECTION],
						$this->getLogicExpression(),
						$this->getAlias()
					);
					
					break;
					
				case self::IN_CONTEXT:
					$argument = $this->getConstantExpression();
					break;
					
				case self::GROUP_BY_CONTEXT:
					$argument = $this->getIdentifierExpression();
					break;
					
				case self::ORDER_BY_CONTEXT:
					$expression = $this->getLogicExpression();
					
					$token = $this->tokenizer->peek();
					if ($this->checkKeyword($token, array('asc', 'desc'))) {
						$direction = $token->getValue() == 'asc';
						$this->tokenizer->next();
						
					} else
						$direction = null;
					
					$argument = new OqlOrderByExpression($expression, $direction);
					
					break;
					
				default:
					new WrongArgumentException("unknown context '{$context}'");
			}
			
			if (!$argument)
				$this->error($message);
			else
				return $argument;
		}
	}
?>