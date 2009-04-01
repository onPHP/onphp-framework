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
			Assert::isNotNull($rule->getRule());
			Assert::isNotNull($rule->getSeparator());
			
			$ruleStrategy = $rule->getRule()->getParseStrategy();
			$separatorStrategy = $rule->getSeparator()->getParseStrategy();
			
			$childNodes = array();
			$separatorNode = null;
			
			// NOTE: rule and separator are mandatory in spite of optional()
			do {
				if (
					$node
					= $ruleStrategy->getNode($rule->getRule(), $tokenizer)
				) {
					$childNodes[] = $node;
				} else {
					if ($separatorNode)
						array_pop($childNodes);
					break;
				}
				
				if (
					$separatorNode
					= $separatorStrategy->getNode($rule->getSeparator(), $tokenizer)
				) {
					$childNodes[] = $separatorNode;
				}
			} while ($separatorNode);
			
			if ($childNodes) {
				return OqlNonterminalNode::create()->setChilds($childNodes);
			} elseif ($rule->isRequired()) {
				// FIXME: error message
				$this->raiseError($tokenizer, 'expected');
			}
			
			return null;
		}
	}
?>