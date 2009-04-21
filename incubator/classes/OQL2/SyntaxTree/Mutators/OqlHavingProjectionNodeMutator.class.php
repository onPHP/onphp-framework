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
	final class OqlHavingProjectionNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlHavingProjectionNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			Assert::isNotNull($node->toValue());
			
			// TODO: nothing more expected assertion?
			
			return OqlObjectProjectionNode::create()->
				setObject(
					Projection::having($node->toValue())
				)->
				setProperty($node->toValue());
		}
	}
?>