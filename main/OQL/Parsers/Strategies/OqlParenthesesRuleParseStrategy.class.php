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

	/**
	 * @ingroup OQL
	**/
	final class OqlParenthesesRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlParenthesesRuleParseStrategy
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function parse(
			OqlGrammarRule $rule,
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			Assert::isTrue($rule instanceof OqlParenthesesRule);
			Assert::isNotNull($innerRule = $rule->getRule());
			
			$index = $tokenizer->getIndex();
			
			try {
				$this->checkParentheses($tokenizer, '(');
				
				// FIXME: error message
				if (!$node = $innerRule->process($tokenizer, $silent))
					$this->raiseError($tokenizer, 'expected');
				
				$this->checkParentheses($tokenizer, ')');
				
				return $node;
			
			} catch (SyntaxErrorException $e) {
				$tokenizer->setIndex($index);
				if (!$silent)
					throw $e;
			}
			
			return null;
		}
		
		/**
		 * @return OqlParenthesesRuleParseStrategy
		**/
		private function checkParentheses(OqlTokenizer $tokenizer, $value)
		{
			if ($this->checkToken($tokenizer->peek(), $value))
				$tokenizer->next();
			else
				$this->raiseError($tokenizer, 'expected "'.$value.'"');
			
			return $this;
		}
		
		private static function checkToken($token, $value)
		{
			return
				$token instanceof OqlToken
				&& $token->getType() == OqlTokenType::PARENTHESES
				&& $token->getValue() == $value;
		}
	}
?>