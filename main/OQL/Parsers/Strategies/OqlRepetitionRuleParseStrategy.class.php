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
		 * @return OqlSyntaxNode
		**/
		public function parse(
			OqlGrammarRule $rule,
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			Assert::isTrue($rule instanceof OqlRepetitionRule);
			Assert::isNotNull($rule->getRule());
			Assert::isNotNull($rule->getSeparator());
			
			$childNodes = array();
			$separatorNode = null;
			
			do {
				if ($node = $rule->getRule()->process($tokenizer, true)) {
					$childNodes[] = $node;
				} else {
					if ($separatorNode)
						array_pop($childNodes);
					break;
				}
				
				if ($separatorNode = $rule->getSeparator()->process($tokenizer, true))
					$childNodes[] = $separatorNode;
			
			} while ($separatorNode);
			
			if ($childNodes) {
				if (count($childNodes) == 1)
					return reset($childNodes);
				else
					return OqlNonterminalNode::create()->setChilds($childNodes);
			
			} elseif (!$silent) {
				// FIXME: error message
				$this->raiseError($tokenizer, 'expected');
			}
			
			return null;
		}
	}
?>