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
	abstract class OqlSyntaxNode extends IdentifiableObject implements Stringable
	{
		private static $globalId = 0;
		
		protected $parent = null;
		
		public function __construct()
		{
			$this->id = self::$globalId++;
		}
		
		abstract public function hasChild(OqlSyntaxNode $child);
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract public function addChild(OqlSyntaxNode $child);
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract public function dropChild(OqlSyntaxNode $child);
		
		abstract public function getChilds();
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract public function setChilds(array $childs);
		
		/**
		 * @return OqlSyntaxNode
		**/
		abstract public function dropChilds();
		
		final public function setId($id)
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getParent()
		{
			return $this->parent;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function setParent(OqlSyntaxNode $parent)
		{
			$this->parent = $parent;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropParent()
		{
			$this->parent = null;
			
			return $this;
		}
	}
?>