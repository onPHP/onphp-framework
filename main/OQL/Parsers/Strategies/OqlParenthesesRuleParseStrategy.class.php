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
	class OqlParenthesesRuleParseStrategy extends OqlGrammarRuleParseStrategy
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
			Assert::isNotNull($rule->getRule());
			
			$index = $tokenizer->getIndex();
			
			try {
				if ($this->checkToken($tokenizer->peek(), '('))
					$tokenizer->next();
				else
					$this->raiseError($tokenizer, 'expected (');
				
				// FIXME: error message
				if (!$node = $rule->getRule()->process($tokenizer, $silent))
					$this->raiseError($tokenizer, 'expected');
				
				if ($this->checkToken($tokenizer->peek(), ')'))
					$tokenizer->next();
				else
					$this->raiseError($tokenizer, 'expected )');
				
				return $node;
			
			} catch (SyntaxErrorException $e) {
				if ($silent)
					$tokenizer->setIndex($index);
				else
					throw $e;
			}
			
			return null;
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