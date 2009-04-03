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
	final class OqlTerminalRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlTerminalRuleParseStrategy
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlTokenNode
		**/
		public function parse(
			OqlGrammarRule $rule,
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			Assert::isTrue($rule instanceof OqlTerminalRule);
			
			$token = $tokenizer->peek();
			
			if (
				$token !== null
				&& $this->checkToken($token, $rule->getType(), $rule->getValue())
			) {
				$tokenizer->next();
				
				return OqlTokenNode::create()->setToken($token);
			
			} elseif (!$silent) {
				// FIXME: error message
				$this->raiseError($tokenizer, 'expected "'.$rule->getValue().'"');
			}
			
			return null;
		}
		
		private static function checkToken(OqlToken $token, $type, $value)
		{
			if ($token->getType() == $type) {
				if ($value === null)
					return true;
				elseif (is_array($value))
					return in_array($token->getValue(), $value);
				else
					return $token->getValue() == $value;
			}
			
			return false;
		}
	}
?>