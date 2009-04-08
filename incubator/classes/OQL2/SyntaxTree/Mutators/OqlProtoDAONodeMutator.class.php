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
	final class OqlProtoDAONodeMutator extends OqlSyntaxNodeMutator
	{
		/**
		 * @return OqlProtoDAONodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlProtoDAONode
		**/
		public function process(OqlSyntaxNode $node)
		{
			$class = $node->toValue();
			
			Assert::isTrue(
				ClassUtils::isClassName($class),
				'invalid class name: '.$class
			);
			Assert::classExists($class);
			Assert::isInstance(
				$class,
				'DAOConnected',
				"class {$class} must implement DAOConnected interface"
			);
			
			// TODO: nothing more expected assertion?
			
			return OqlProtoDAONode::create()->setObject(
				call_user_func(array($class, 'dao'))
			);
		}
	}
?>