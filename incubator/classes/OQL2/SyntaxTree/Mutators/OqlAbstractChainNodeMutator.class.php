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
	abstract class OqlAbstractChainNodeMutator extends OqlSyntaxNodeMutator
	{
		const SEPARATOR = ',';
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract protected function makeChainNode(array $list);
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function process(OqlSyntaxNode $node)
		{
			$list = array();
			
			$iterator = OqlSyntaxTreeRecursiveIterator::create();
			$current = $iterator->reset($node);
			
			while ($current) {
				if ($current->toValue() !== self::SEPARATOR)
					$list[] = $current;
				
				$current = $iterator->next();
			}
			
			if (count($list) == 1)
				return reset($list);
			else
				return $this->makeChainNode($list);
			
			Assert::isUnreachable();
		}
	}
?>