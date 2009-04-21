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
	final class OqlPlaceholderNodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlPlaceholderNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlPlaceholderNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			Assert::isTrue(
				$node instanceof OqlTokenNode
				&& $node->getToken() !== null
				&& $node->getToken()->getType() == OqlTokenType::PLACEHOLDER
			);
			
			Assert::isTrue($rootNode instanceof OqlBindableNodeWrapper);
			
			return OqlPlaceholderNode::create()->
				setPlacehoder(
					$rootNode->getPool()->spawn($node->toValue())
				);
		}
	}
?>