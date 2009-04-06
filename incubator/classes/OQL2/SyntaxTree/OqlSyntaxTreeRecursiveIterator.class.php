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
	final class OqlSyntaxTreeRecursiveIterator extends Singleton
		implements Instantiatable
	{
		private $node = null;
		
		/**
		 * @return OqlSyntaxTreeRecursiveIterator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function reset(OqlSyntaxNode $node)
		{
			return $this->node = $node;
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
				if ($child = $node->getFirstChild())
					$node = $child;
				else
					$node = $node->getNextSibling();
			
			} while ($node instanceof OqlNonterminalNode);
			
			return $this->node = $node;
		}
	}
?>