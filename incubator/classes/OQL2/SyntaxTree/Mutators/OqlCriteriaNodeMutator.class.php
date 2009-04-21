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
	final class OqlCriteriaNodeMutator extends OqlSyntaxNodeMutator
	{
		private static $methodMap = array(
			'from'		=> 'setDao',
			'where'		=> 'add',
			'order by'	=> 'addOrder',
			'limit'		=> 'setLimit',
			'offset'	=> 'setOffset',
			'having'	=> null
		);
		
		/**
		 * @return OqlCriteriaNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlCriteriaNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			$criteria = Criteria::create();
			
			$iterator = OqlSyntaxTreeRecursiveIterator::create();
			$current = $iterator->reset($node);
			
			while ($current) {
				// properties, group by, having projections
				if ($current instanceof OqlObjectProjectionNode) {
					$criteria->addProjection($current->toValue());
				
				// from, where, order by, limit, offset
				} elseif ($current instanceof OqlTokenNode) {
					Assert::isIndexExists(self::$methodMap, $current->toValue());
					
					if ($setter = self::$methodMap[$current->toValue()]) {
						$next = $iterator->next();
						Assert::isNotNull($next);
						
						$criteria->{$setter}($next->toValue());
					}
				}
				
				$current = $iterator->next();
			}
			
			return OqlCriteriaNode::create()->setObject($criteria);
		}
	}
?>