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
	class OqlSequenceRule extends OqlListedRule
	{
		/**
		 * @return OqlSequenceRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		protected function buildTerminals()
		{
			$collect = true;
			
			foreach ($this->list as $rule) {
				$rule->build();
				
				if ($collect)
					$this->terminals = array_merge(
						$this->terminals,
						$rule->getTerminals()
					);
				
				if (!$rule instanceof OqlOptionalRule)
					$collect = false;
			}
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		protected function parse(
			OqlTokenizer $tokenizer,
			OqlSyntaxNode $rootNode,
			$silent = false
		)
		{
			$index = $tokenizer->getIndex();
			
			try {
				$childNodes = array();
				
				if ($this->match($tokenizer->peek())) {
					foreach ($this->list as $rule) {
						if ($node = $rule->process($tokenizer, $rootNode, false))
							$childNodes[] = $node;
					}
				}
				
				// FIXME: error message
				if (!$childNodes)
					$this->raiseError($tokenizer, 'expected');
				
				if (count($childNodes) == 1)
					return reset($childNodes);
				else
					return OqlNonterminalNode::create()->setChilds($childNodes);
			
			} catch (OqlSyntaxErrorException $e) {
				$tokenizer->setIndex($index);
				if (!$silent)
					throw $e;
			}
			
			return null;
		}
	}
?>