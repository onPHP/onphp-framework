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
	class OqlRepetitionRule extends OqlGrammarRule
	{
		protected $rule			= null;
		protected $separator	= null;
		
		/**
		 * @return OqlRepetitionRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlRepetitionRuleParseStrategy
		**/
		public function getParseStrategy()
		{
			return OqlRepetitionRuleParseStrategy::me();
		}
		
		/**
		 * @return OqlGrammarRule
		**/
		public function getRule()
		{
			return $this->rule;
		}
		
		/**
		 * @return OqlRepetitionRule
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
		 * @return OqlRepetitionRule
		**/
		public function setSeparator(OqlGrammarRule $separator)
		{
			$this->separator = $separator;
			
			return $this;
		}
	}
?>