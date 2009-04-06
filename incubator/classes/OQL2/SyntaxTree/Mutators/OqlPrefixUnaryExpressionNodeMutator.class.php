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
	final class OqlPrefixUnaryExpressionNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlPrefixUnaryExpressionNodeMutator
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
			
			$operator = $node;
			if ($operator instanceof OqlNonterminalNode)
				$operator = $iterator->next();
			
			if ($operator === null)
				return $node;
			
			if (($operand = $iterator->next()) === null)
				return $node;
			
			return OqlLogicalObjectNode::create()->setObject( 
				new PrefixUnaryExpression(
					$operator->toValue(),
					$operand->toValue()
				)
			);
		}
	}
?>