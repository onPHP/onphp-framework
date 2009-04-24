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
	abstract class OqlDecoratedRule extends OqlGrammarRule
	{
		protected $rule = null;
		
		/**
		 * @return OqlGrammarRule
		**/
		public function getRule()
		{
			return $this->rule;
		}
		
		/**
		 * @return OqlOptionalRule
		**/
		public function setRule(OqlGrammarRule $rule)
		{
			$this->rule = $rule;
			
			return $this;
		}
		
		/**
		 * @return OqlDecoratedRule
		**/
		protected function buildTerminals()
		{
			$this->rule->build();
			
			return $this;
		}
		
		protected function getTerminals()
		{
			return $this->rule->getTerminals();
		}
	}
?>