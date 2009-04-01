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
	final class OqlParenthesesRule extends OqlGrammarRule
	{
		/**
		 * @return OqlParenthesesRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlParenthesesRuleParseStrategy
		**/
		public function getParseStrategy()
		{
			return OqlParenthesesRuleParseStrategy::me();
		}
		
		public function getRule()
		{
			return $this->rule;
		}
		
		/**
		 * @return OqlParenthesesRule
		**/
		public function setRule(OqlGrammarRule $rule)
		{
			$this->rule = $rule;
			
			return $this;
		}
	}
?>