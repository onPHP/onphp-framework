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
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function parse(OqlGrammarRule $rule, OqlTokenizer $tokenizer)
		{
			Assert::isTrue($rule instanceof OqlRepetitionRule);
			
			$ruleStrategy = $rule->getRule()->getParseStrategy();
			$separatorStrategy = $rule->getSeparator()->getParseStrategy();
			
			$parentNode = null;
			
			do {
				if (
					$node = $ruleStrategy->getNode($rule->getRule(), $tokenizer)
				) {
					if ($parentNode === null)
						$parentNode = OqlNonterminalNode::create();
					
					$parentNode->addChild($node);
				}
			
			} while (
				$separatorStrategy->getNode($rule->getSeparator(), $tokenizer)
			);
			
			// FIXME: error message
			if ($rule->isRequired())
				$this->raiseError($tokenizer, 'expected');
			
			return $parentNode;
		}
	}
?>