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
	class OqlRepetitionRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlRepetitionRuleParseStrategy
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function parse(OqlGrammarRule $rule, OqlTokenizer $tokenizer)
		{
			Assert::isInstance($rule, 'OqlRepetitionRule');
			
			$ruleStrategy = $rule->getRule()->getParseStrategy();
			$separatorStrategy = $rule->getSeparator()->getParseStrategy();
			$list = array();
			
			do {
				if (
					$node = $ruleStrategy->getNode($rule->getRule(), $tokenizer)
				) {
					$list[] = $node;
				}
			
			} while (
				$separatorStrategy->getNode($rule->getSeparator(), $tokenizer)
			);
			
			// FIXME: error message
			if ($rule->isRequired())
				$this->raiseError($tokenizer, 'expected');
			
			// FIXME: return syntax tree node
			return $list;
		}
	}
?>