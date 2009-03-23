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
	class OqlSequenceRule extends OqlGrammarRule
	{
		protected $rule			= null;
		protected $separator	= null;
		
		/**
		 * @return OqlSequenceRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSequenceRuleParseStrategy
		**/
		public function getParseStrategy()
		{
			return OqlSequenceRuleParseStrategy::me();
		}
		
		/**
		 * @return OqlGrammarRule
		**/
		public function getRule()
		{
			return $this->rule;
		}
		
		/**
		 * @return OqlSequenceRule
		**/
		public function setRule(OqlGrammarRule $rule)
		{
			$this->rule = $rule;
			
			return $this;
		}
		
		/**
		 * @return OqlGrammarRule
		**/
		public function getSeparator()
		{
			return $this->separator;
		}
		
		/**
		 * @return OqlSequenceRule
		**/
		public function setSeparator(OqlGrammarRule $separator)
		{
			$this->separator = $separator;
			
			return $this;
		}
	}
?>