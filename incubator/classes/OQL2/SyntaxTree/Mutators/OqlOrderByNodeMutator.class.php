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
	final class OqlOrderByNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlOrderByNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlOrderNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			$iterator = OqlSyntaxTreeDeepRecursiveIterator::me();
			
			$field = $iterator->reset($node);
			Assert::isNotNull($field);
			
			$order = OrderBy::create($field->toValue());
			
			if ($direction = $iterator->next())
				$order->setDirection($direction->toValue() != 'desc');
			
			// TODO: nothing more expected assertion?
			
			return OqlOrderNode::create()->setObject($order);
		}
	}
?>