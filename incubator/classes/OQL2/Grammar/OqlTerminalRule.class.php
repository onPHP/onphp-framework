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
	class OqlTerminalRule extends OqlGrammarRule
	{
		protected $type		= null;
		protected $value	= null;
		
		/**
		 * @return OqlTerminalRule
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlTerminalRuleParseStrategy
		**/
		public function getParseStrategy()
		{
			return OqlTerminalRuleParseStrategy::me();
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		public function setType($type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		/**
		 * @return OqlTokenNode
		**/
		protected function parse(
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			$token = $tokenizer->peek();
			
			if (
				$token !== null
				&& $this->checkToken($token, $this->type, $this->value)
			) {
				$tokenizer->next();
				
				return OqlTokenNode::create()->setToken($token);
			
			} elseif (!$silent) {
				// FIXME: error message
				$this->raiseError($tokenizer, 'expected "'.$this->value.'"');
			}
			
			return null;
		}
		
		private static function checkToken(OqlToken $token, $type, $value)
		{
			if ($token->getType() == $type) {
				if ($value === null)
					return true;
				elseif (is_array($value))
					return in_array($token->getValue(), $value);
				else
					return $token->getValue() == $value;
			}
			
			return false;
		}
	}
?>