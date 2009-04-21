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
	final class OqlProjectionClause extends OqlBindableNodeWrapper
	{
		/**
		 * @return OqlProjectionClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function toProjection()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->toProjection();
		}
		
		protected function checkNode(OqlSyntaxNode $node)
		{
			Assert::isTrue($node instanceof OqlObjectProjectionNode);
		}
	}
?>