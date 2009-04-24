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
	final class OqlProjectionNodeMutator extends OqlSyntaxNodeMutator
	{
		private static $projectionMap = array(
			'sum'	=> 'sum',
			'avg'	=> 'avg',
			'min'	=> 'min',
			'max'	=> 'max',
			'count'	=> 'count'
		);
		
		/**
		 * @return OqlProjectionNodeMutator
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
			$iterator = OqlSyntaxTreeRecursiveIterator::me();
			
			$current = $iterator->reset($node);
			Assert::isNotNull($current);
			
			if ($current->toValue() == 'distinct') {
				Assert::isTrue($rootNode instanceof OqlSelectQuery);
				$rootNode->setDistinct(true);
				
				$aggregate = $iterator->next();
			} else
				$aggregate = $current;
			
			$current = $iterator->next();
			
			if (
				isset(self::$projectionMap[$aggregate->toValue()])
				&& $current !== null
				&& $current->toValue() == '('
			) {
				$current = $iterator->next();
				Assert::isNotNull($current);
				
				if (
					$aggregate->toValue() == 'count'
					&& $current->toValue() == 'distinct'
				) {
					$funcName = 'distinctCount';
					$property = $iterator->next();
				
				} else {
					$funcName = self::$projectionMap[$aggregate->toValue()];
					$property = $current;
				}
				
				Assert::isNotNull($property);
				
				$current = $iterator->next();	// skip )
				Assert::isTrue($current !== null && $current->toValue() == ')');
				
				$current = $iterator->next();	// as (if any)
			
			} else {
				$funcName = 'property';
				$property = $aggregate;
			}
			
			if (
				$current !== null
				&& $current->toValue() == 'as'
			) {
				$alias = $iterator->next();
				Assert::isNotNull($alias);
				$aliasName = $alias->toValue(); 
			} else
				$aliasName = null;
			
			return OqlObjectProjectionNode::create()->
				setObject(
					call_user_func_array(
						array('Projection', $funcName),
						array($property->toValue(), $aliasName)
					)
				)->
				setProperty($property->toValue());
		}
	}
?>