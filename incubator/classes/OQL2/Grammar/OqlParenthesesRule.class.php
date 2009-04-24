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
	final class OqlParenthesesRule extends OqlDecoratedRule
	{
		/**
		 * @return OqlParenthesesRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlParenthesesRule
		**/
		protected function buildTerminals()
		{
			parent::buildTerminals();
			
			$token = OqlToken::create(OqlTokenType::PARENTHESES, '(');
			$this->terminals[$token->toKey()] = $token;
			
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
			Assert::isNotNull($this->rule);
			
			$index = $tokenizer->getIndex();
			
			try {
				$this->checkParentheses($tokenizer, '(');
				
				// FIXME: error message
				if (!$node = $this->rule->process($tokenizer, $rootNode, $silent))
					$this->raiseError($tokenizer, 'expected');
				
				$this->checkParentheses($tokenizer, ')');
				
				return $node;
			
			} catch (OqlSyntaxErrorException $e) {
				$tokenizer->setIndex($index);
				if (!$silent)
					throw $e;
			}
			
			return null;
		}
		
		/**
		 * @return OqlParenthesesRule
		**/
		private function checkParentheses(OqlTokenizer $tokenizer, $value)
		{
			if (
				($token = $tokenizer->peek())
				&& $token->match(OqlTokenType::PARENTHESES, $value)
			)
				$tokenizer->next();
			else
				$this->raiseError($tokenizer, 'expected "'.$value.'"');
			
			return $this;
		}
	}
?>