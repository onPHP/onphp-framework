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
		
		/**
		 * @return OqlSyntaxNode
		**/
		protected function parse(
			OqlTokenizer $tokenizer,
			$silent = false
		)
		{
			foreach ($this->list as $rule) {
				if ($node = $rule->process($tokenizer, true))
					return $node;
			}
			
			// FIXME: error message
			if (!$silent)
				$this->raiseError($tokenizer, 'expected');
			
			return null;
		}
	}
?>