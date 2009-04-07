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
	abstract class OqlSyntaxTreeIterator
	{
		protected $node = null;
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract public function next();
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function reset(OqlSyntaxNode $node)
		{
			$this->node = $node;
			
			if ($this->node instanceof OqlNonterminalNode)
				$this->next();
			
			return $this->node;
		}
	}
?>