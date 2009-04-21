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
	class OqlOptionalRule extends OqlDecoratedRule
	{
		/**
		 * @return OqlOptionalRule
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
			OqlSyntaxNode $rootNode,
			$silent = false
		)
		{
			Assert::isNotNull($this->rule);
			
			return $this->rule->process($tokenizer, $rootNode, true);
		}
	}
?>