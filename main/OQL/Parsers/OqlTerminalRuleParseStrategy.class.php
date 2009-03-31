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
	class OqlTerminalRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlTerminalRuleParseStrategy
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function parse(OqlGrammarRule $rule, OqlTokenizer $tokenizer)
		{
			Assert::isTrue($rule instanceof OqlTerminalRule);
			
			$token = $tokenizer->peek();
			
			if (
				$token !== null
				&& $this->checkToken($token, $rule->getType(), $rule->getValue())
			) {
				$tokenizer->next();
				
				// FIXME: return syntax tree node
				return $token;
			
			} elseif ($rule->isRequired()) {
				// FIXME: error message
				$this->raiseError($tokenizer, 'expected');
			}
			
			return null;
		}
		
		private static function checkToken(OqlToken $token, $type, $value)
		{
			if ($token->getType() == $type) {
				if ($value === null) {
					return true;
				
				} elseif (is_array($value)) {
					return in_array($token->getValue(), $value);
				
				} else {
					return $token->getValue() == $value;
				}
			}
			
			return false;
		}
	}
?>