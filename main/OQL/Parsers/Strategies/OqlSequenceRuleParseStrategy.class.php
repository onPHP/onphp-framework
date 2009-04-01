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
	class OqlSequenceRuleParseStrategy extends OqlGrammarRuleParseStrategy
	{
		/**
		 * @return OqlSequenceRuleParseStrategy
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function parse(
			OqlGrammarRule $rule,
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			Assert::isTrue($rule instanceof OqlSequenceRule);
			
			try {
				$index = $tokenizer->getIndex();
				$childNodes = array();
				
				foreach ($rule->getList() as $ruleItem) {
					if (
						$node
						= $ruleItem->getParseStrategy()->parse($ruleItem, $tokenizer, false)
					) {
						$childNodes[] = $node;
					}
				}
				
				return $childNodes
					? OqlNonterminalNode::create()->setChilds($childNodes)
					: null;
			
			} catch (SyntaxErrorException $e) {
				if (!$silent && $rule->isRequired())
					throw $e;
				else
					$tokenizer->setIndex($index);
			}
			
			return null;
		}
	}
?>