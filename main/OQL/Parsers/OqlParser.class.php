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
	final class OqlParser
	{
		private $grammar	= null;
		private $ruleId		= null;
		
		/**
		 * @return OqlParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlGrammar
		**/
		public function getGrammar()
		{
			return $this->grammar;
		}
		
		/**
		 * @return OqlParser
		**/
		public function setGrammar(OqlGrammar $grammar)
		{
			$this->grammar = $grammar;
			
			return $this;
		}
		
		public function getRuleId()
		{
			return $this->ruleId;
		}
		
		/**
		 * @return OqlParser
		**/
		public function setRuleId($ruleId)
		{
			$this->ruleId = $ruleId;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function parse($string)
		{
			Assert::isString($string);
			Assert::isNotNull($this->grammar, 'grammar must be set');
			Assert::isNotNull($this->ruleId);
			
			$rule = $this->grammar->get($this->ruleId);
			
			return $rule->getParseStrategy()->parse(
				$rule,
				new OqlTokenizer($string)
			);
		}
	}
?>