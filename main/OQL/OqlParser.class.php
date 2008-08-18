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
	 * @ingroup OQL
	**/
	abstract class OqlParser
	{
		const INITIAL_STATE		= 254;
		const FINAL_STATE		= 255;
		
		protected $state		= null;
		protected $tokenizer	= null;
		protected $query		= null;
		
		protected $parentheses	= null;
		
		/**
		 * @return OqlQuery
		**/
		abstract protected function makeQuery();
		
		abstract protected function handleState();
		
		/**
		 * @throws WrongStateException
		 * @throws SyntaxErrorException
		 * @return OqlQuery
		**/
		public function parse($string)
		{
			Assert::isString($string);
			
			$this->tokenizer = new OqlTokenizer($string);
			$this->state = self::INITIAL_STATE;
			$this->query = $this->makeQuery()->setQuery($string);
			$this->parentheses = 0;
			
			while ($this->state != self::FINAL_STATE)
				$this->state = $this->handleState();
			
			$this->checkParentheses();
			
			if ($token = $this->tokenizer->peek())
				$this->error("unexpected: {$this->getTokenValue($token, true)}");
			
			return $this->query;
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
		 * @return OqlQueryParameter
		**/
		protected function getArithmeticArgumentExpression()
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
		
		/**
		 * @return OqlQueryParameter
		**/
		protected function getArgumentExpression($context, $message)
		{
			return null;
		}
		
		protected function getCommaSeparatedList($context, $message)
		{
			$isComma = false;
			$list = array();
			
			do {
				if ($isComma)
					$this->tokenizer->next();
				
				$list[] = $this->getArgumentExpression($context, $message);
				
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
	}
?>