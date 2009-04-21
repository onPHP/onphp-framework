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
	final class OqlBinaryExpressionNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlBinaryExpressionNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlLogicalObjectNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			$iterator = OqlSyntaxTreeDeepRecursiveIterator::me();
			
			if (($left = $iterator->reset($node)) === null)
				return $node;
			
			if (($operator = $iterator->next()) === null)
				return $node;
			
			if (($right = $iterator->next()) === null)
				return $node;
			
			// TODO: assertions?
			
			return OqlLogicalObjectNode::create()->setObject( 
				new BinaryExpression(
					$left->toValue(),
					$right->toValue(),
					$operator->toValue()
				)
			);
		}
	}
?>