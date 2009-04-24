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
	class OqlAlternationRule extends OqlListedRule
	{
		/**
		 * @return OqlAlternationRule
		**/
		public static function create()
		{
			return new self;
		}
		
		protected function getMatches($token)
		{
			if ($token instanceof OqlToken) {
				// FIXME: return first match only
				return $this->list;
			}
			
			return array();
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
			if ($list = $this->getMatches($tokenizer->peek())) {
				foreach ($list as $rule) {
					if ($node = $rule->process($tokenizer, $rootNode, true))
						return $node;
				}
			}
			
			// FIXME: error message
			if (!$silent)
				$this->raiseError($tokenizer, 'expected');
			
			return null;
		}
	}
?>