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
	final class OqlOrderClause extends OqlBindableNodeWrapper
	{
		/**
		 * @return OqlOrderClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return MappableObject
		**/
		public function toOrder()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->toOrder();
		}
		
		protected function checkNode(OqlSyntaxNode $node)
		{
			Assert::isTrue($node instanceof OqlOrderNode);
		}
	}
?>