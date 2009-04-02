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
					if ($node = $ruleItem->process($tokenizer, false))
						$childNodes[] = $node;
				}
				
				switch (count($childNodes)) {
					case 0:
						return null;
					
					case 1:
						return reset($childNodes);
					
					default:
						return OqlNonterminalNode::create()->
							setChilds($childNodes);
				}
			
			} catch (SyntaxErrorException $e) {
				if ($silent)
					$tokenizer->setIndex($index);
				else
					throw $e;
			}
			
			return null;
		}
	}
?>