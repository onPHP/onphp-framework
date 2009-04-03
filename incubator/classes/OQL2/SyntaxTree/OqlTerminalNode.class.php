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
	abstract class OqlTerminalNode extends OqlSyntaxNode
	{
		public function hasChild(OqlSyntaxNode $child)
		{
			return false;
		}
		
		/**
		 * @throws UnsupportedMethodException
		**/
		public function addChild(OqlSyntaxNode $child)
		{
			throw new UnsupportedMethodException("terminal node can't have children");
		}
		
		/**
		 * @throws UnsupportedMethodException
		**/
		public function dropChild(OqlSyntaxNode $child)
		{
			throw new UnsupportedMethodException("terminal node can't have children");
		}
		
		public function getChilds()
		{
			return array();
		}
		
		/**
		 * @throws UnsupportedMethodException
		**/
		public function setChilds(array $childs)
		{
			throw new UnsupportedMethodException("terminal node can't have children");
		}
		
		/**
		 * @throws UnsupportedMethodException
		**/
		public function dropChilds()
		{
			throw new UnsupportedMethodException("terminal node can't have children");
		}
	}
?>