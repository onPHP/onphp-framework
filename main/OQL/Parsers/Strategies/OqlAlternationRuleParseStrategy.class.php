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
	class OqlAlternationRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlAlternationRuleParseStrategy
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
			Assert::isTrue($rule instanceof OqlAlternationRule);
			
			foreach ($rule->getList() as $ruleItem) {
				if ($node = $ruleItem->process($tokenizer, true))
					return $node;
			}
			
			// FIXME: error message
			if (!$silent && $rule->isRequired())
				$this->raiseError($tokenizer, 'expected');
			
			return null;
		}
	}
?>