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
	final class OqlPostfixUnaryExpressionNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlPostfixUnaryExpressionNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlLogicalObjectNode
		**/
		public function process(OqlSyntaxNode $node)
		{
			$iterator = OqlSyntaxTreeRecursiveIterator::me();
			$iterator->reset($node);
			
			$operand = $node;
			if ($operand instanceof OqlNonterminalNode)
				$operand = $iterator->next();
			
			if ($operand === null)
				return $node;
			
			if (($operator = $iterator->next()) === null)
				return $node;
			
			return OqlLogicalObjectNode::create()->setObject( 
				new PostfixUnaryExpression(
					$operand->toValue(),
					$operator->toValue()
				)
			);
		}
	}
?>