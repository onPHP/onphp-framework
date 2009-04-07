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
	final class OqlSyntaxTreeRecursiveIterator extends OqlSyntaxTreeIterator
	{
		private $stack		= array();
		private $visited	= array();
		
		/**
		 * @return OqlSyntaxTreeRecursiveIterator
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function reset(OqlSyntaxNode $node)
		{
			$this->stack = array();
			$this->visited = array();
			
			return parent::reset($node);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function next()
		{
			if ($this->node === null)
				return null;
			
			$node = $this->node;
			
			do {
				$this->markNodeVisited($node);
				$nodeParent = $node->getParent();
				
				if (
					($child = $node->getFirstChild())
					&& !$this->isNodeVisited($child)
				) {
					array_push($this->stack, $node);
					$node = $child;
				
				} elseif (
					($node = $node->getNextSibling())
					&& $this->isNodeVisited($node)
				) {
					$node = null;
				}
				
				if ($node === null)
					$node = array_pop($this->stack);
				
				if ($node === null)
					$node = $nodeParent;
			
			} while ($node instanceof OqlNonterminalNode);
			
			return $this->node = $node;
		}
		
		/**
		 * @return OqlSyntaxTreeRecursiveIterator
		**/
		private function markNodeVisited(OqlSyntaxNode $node)
		{
			$this->visited[$node->getId()] = true;
			
			return $this;
		}
		
		private function isNodeVisited(OqlSyntaxNode $node)
		{
			return isset($this->visited[$node->getId()]);
		}
	}
?>