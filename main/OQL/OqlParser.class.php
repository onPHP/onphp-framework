<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	abstract class OqlParser
	{
		const INITIAL_STATE		= 254;
		const FINAL_STATE		= 255;
		
		// class map
		const PREFIX_UNARY_EXPRESSION	= 1;
		const POSTFIX_UNARY_EXPRESSION	= 2;
		const BINARY_EXPRESSION			= 3;
		const BETWEEN_EXPRESSION		= 4;
		
		private static $classMap = array(
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
		
		protected $state		= null;
		protected $tokenizer	= null;
		protected $oqlObject	= null;
		
		protected $parentheses	= null;
		
		/**
		 * @return OqlQueryClause
		**/
		abstract protected function makeOqlObject();
		
		abstract protected function handleState();
		
		/**
		 * @return OqlQueryClause
		**/
		public function parse($string = null)
		{
			if ($string === null) {
				Assert::isNotNull($this->tokenizer);
			
			} else {
				Assert::isString($string);
				$this->tokenizer = new OqlTokenizer($string);
			}
			
			$this->state = self::INITIAL_STATE;
			$this->oqlObject = $this->makeOqlObject()->setQuery($string);
			$this->parentheses = 0;
			
			while ($this->state != self::FINAL_STATE)
				$this->state = $this->handleState();
			
			$this->checkParentheses();
			
			if ($token = $this->tokenizer->peek())
				$this->error("unexpected: {$this->getTokenValue($token, true)}");
			
			return $this->oqlObject;
		}
		
		/**
		 * @return OqlTokenizer
		**/
		public function getTokenizer()
		{
			return $this->tokenizer;
		}
		
		/**
		 * @return OqlParser
		**/
		public function setTokenizer(OqlTokenizer $tokenizer)
		{
			$this->tokenizer = $tokenizer;
			
			return $this;
		}
		
		protected function getTokenValue($token, $raw = false)
		{
			if ($token instanceof OqlToken)
				return $raw
					? $token->getRawValue()
					: $token->getValue();
				
			return null;
		}
		
		protected function checkToken($token, $type, $value = null)
		{
			if (
				$token instanceof OqlToken
				&& $token->getType() == $type
			) {
				if (is_null($value))
					return true;
					
				elseif (is_array($value))
					return in_array($token->getValue(), $value);
					
				else
					return $token->getValue() == $value;
			}
			
			return false;
		}
		
		protected function checkKeyword($token, $value)
		{
			return $this->checkToken($token, OqlToken::KEYWORD, $value);
		}
		
		protected function checkIdentifier($token)
		{
			if ($token instanceof OqlToken) {
				if ($token->getType() == OqlToken::IDENTIFIER)
					return true;
				
				// fix token value if identifier name is equal to
				// reserved word or aggregate function name
				elseif (
					$token->getType() == OqlToken::KEYWORD
					|| $token->getType() == OqlToken::AGGREGATE_FUNCTION
				) {
					$token->setValue($token->getRawValue());
					
					return true;
				}
			}
			
			return false;
		}
		
		protected function checkConstant($token)
		{
			return
				$token instanceof OqlToken
				&& (
					$token->getType() == OqlToken::STRING
					|| $token->getType() == OqlToken::NUMBER
					|| $token->getType() == OqlToken::BOOLEAN
					|| $token->getType() == OqlToken::NULL
					|| $token->getType() == OqlToken::SUBSTITUTION
				);
		}
		
		protected function checkUnaryMinus($token)
		{
			return $this->checkToken($token, OqlToken::ARITHMETIC_OPERATOR, '-');
		}
		
		/**
		 * @throws SyntaxErrorException
		**/
		protected function checkParentheses($message = null)
		{
			if ($this->openParentheses(false, $message))
				$this->error("unexpected '(' {$message}");
				
			elseif ($this->closeParentheses(false, $message))
				$this->error("unexpected ')' {$message}");
				
			if ($this->parentheses > 0)
				$this->error("unexpected '(' {$message}");
			
			return true;
		}
		
		/**
		 * @throws SyntaxErrorException
		**/
		protected function openParentheses($required, $message = null)
		{
			if (
				$this->checkToken($this->tokenizer->peek(), OqlToken::PARENTHESES, '(')
			) {
				$this->tokenizer->next();
				$this->parentheses++;
				
				return true;
				
			} elseif ($required)
				$this->error("expecting ')' {$message}");
			
			return false;
		}
		
		/**
		 * @throws SyntaxErrorException
		**/
		protected function closeParentheses($required, $message = null)
		{
			if (
				$this->checkToken($this->tokenizer->peek(), OqlToken::PARENTHESES, ')')
			) {
				$this->tokenizer->next();
				$this->parentheses--;
				if ($this->parentheses < 0)
					$this->error("unexpected ')' {$message}");
				
				return true;
				
			} elseif ($required)
				$this->error("expecting ')' {$message}");
			
			return false;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function getIdentifierExpression()
		{
			if ($isUnaryMinus = $this->checkUnaryMinus($this->tokenizer->peek()))
				$this->tokenizer->next();
			
			$token = $this->tokenizer->peek();
			
			if ($this->checkIdentifier($token)) {
				$this->tokenizer->next();
				
				return $this->makeQuerySignedExpression($token, $isUnaryMinus);
			}
			
			return null;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function getConstantExpression()
		{
			if ($isUnaryMinus = $this->checkUnaryMinus($this->tokenizer->peek()))
				$this->tokenizer->next();
			
			$token = $this->tokenizer->peek();
			
			if (
				$token instanceof OqlToken
				&& (
					(
						!$isUnaryMinus
						&& (
							$token->getType() == OqlToken::STRING
							|| $token->getType() == OqlToken::BOOLEAN
							|| $token->getType() == OqlToken::NULL
						)
					) || (
						$token->getType() == OqlToken::NUMBER
						|| $token->getType() == OqlToken::SUBSTITUTION
					)
				)
			) {
				$this->tokenizer->next();
				
				return $this->makeQuerySignedExpression($token, $isUnaryMinus);
			}
			
			return null;
		}
		
		/**
		 * @return OqlQueryExpression
		**/
		protected function getLogicExpression(
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
						0,	// FIXME: remove
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
		protected function getArithmeticExpression(
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
		
		// FIXME: drop context, message
		/**
		 * @return OqlQueryParameter
		**/
		protected function getArgumentExpression($context, $message)
		{
			return $this->getConstantExpression();
		}
		
		// FIXME: drop context
		protected function getCommaSeparatedList($context, $message)
		{
			$isComma = false;
			$list = array();
			
			do {
				if ($isComma)
					$this->tokenizer->next();
				
				if ($argument = $this->getArgumentExpression($context, $message))
					$list[] = $argument;
				else
					$this->error($message);
				
			} while (
				$isComma = $this->checkToken($this->tokenizer->peek(), OqlToken::PUNCTUATION, ',')
			);
			
			return $list;
		}
		
		/**
		 * @return OqlQueryExpression
		**/
		protected function makeQueryExpression($className /*, ... */)
		{
			$expression = OqlQueryExpression::create()->
				setClassName($className);
				
			$arguments = func_get_args();
			reset($arguments);
			$argument = next($arguments);
			
			while ($argument) {
				$expression->addParameter(
					$this->makeQueryParameter($argument)
				);
				
				$argument = next($arguments);
			}
			
			return $expression;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function makeQuerySignedExpression($argument, $isUnaryMinus)
		{
			$expression = $this->makeQueryParameter($argument);
			if ($isUnaryMinus)
				$expression = new OqlPrefixMinusExpression($expression);
			
			return $expression;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function makeQueryParameter($argument)
		{
			if ($argument instanceof OqlQueryParameter)
				return $argument;
				
			elseif ($argument instanceof OqlToken)
				return OqlQueryParameter::create()->
					setValue($argument->getValue())->
					setBindable($argument->getType() == OqlToken::SUBSTITUTION);
				
			else
				return OqlQueryParameter::create()->
					setValue($argument);
		}
		
		/**
		 * @throws SyntaxErrorException
		**/
		protected function error($message)
		{
			throw new SyntaxErrorException(
				$message,
				$this->tokenizer->getLine(),
				$this->tokenizer->getPosition()
			);
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
		 * @return OqlQueryParameter
		**/
		private function getArithmeticArgumentExpression()
		{
			$token = $this->tokenizer->peek();
			
			if (
				$this->checkIdentifier($token)
				|| $this->checkToken($token, OqlToken::NUMBER)
				|| $this->checkToken($token, OqlToken::SUBSTITUTION)
			) {
				$this->tokenizer->next();
				
				return $this->makeQueryParameter($token);
			}
			
			return null;
		}
	}
?>