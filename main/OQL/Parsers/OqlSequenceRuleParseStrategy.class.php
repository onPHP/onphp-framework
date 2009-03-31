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
		 * @return OqlSyntaxNode
		**/
		public function parse(OqlGrammarRule $rule, OqlTokenizer $tokenizer)
		{
			Assert::isTrue($rule instanceof OqlSequenceRule);
			
			$parentNode = null;
			
			foreach ($rule->getList() as $ruleItem) {
				if (
					$node
					= $ruleItem->getParseStrategy()->parse($ruleItem, $tokenizer)
				) {
					if ($parentNode === null)
						$parentNode = OqlSyntaxNode::create();
					
					$parentNode->addChild($node);
				}
			}
			
			return $parentNode;
		}
	}
?>