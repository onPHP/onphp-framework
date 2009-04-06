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
	final class OqlBetweenExpressionNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlInExpressionNodeMutator
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
			
			var_dump($node->toString());
			
			if (($field = $iterator->reset($node)) === null)
				return $node;
			
			Assert::isTrue(
				($keyword = $iterator->next())
				&& $keyword->toValue() == 'between'
			);
			
			Assert::isNotNull($left = $iterator->next());
			
			Assert::isTrue(
				($keyword = $iterator->next())
				&& $keyword->toValue() == 'and'
			);
			
			Assert::isNotNull($right = $iterator->next());
			
			Assert::isNull($iterator->next());
			
			return OqlLogicalObjectNode::create()->setObject(
				Expression::between(
					$field->toValue(),
					$left->toValue(),
					$right->toValue()
				)
			);
		}
	}
?>