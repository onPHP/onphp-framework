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
	class OqlSyntaxNode extends IdentifiableObject
	{
		private static $globalId = 0;
		
		protected $parent	= null;
		protected $childs	= array();
		
		protected $token	= null;
		
		/**
		 * @return OqlSyntaxNode
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->id = self::$globalId++;
		}
		
		public function __destruct()
		{
			$this->dropChilds();
			if (isset($this->parent))
				$this->parent->dropChild($this);
		}
		
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
			if ($this->parent !== $parent) {
				if ($this->parent)
					$this->parent->dropChild($this);
				
				$this->parent = $parent;
				
				$this->parent->addChild($this);
			}
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropParent()
		{
			if ($this->parent) {
				$this->parent->dropChild($this);
				$this->parent = null;
			}
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function hasChild(OqlSyntaxNode $child)
		{
			return isset($this->childs[$child->getId()]);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getChild(OqlSyntaxNode $child)
		{
			return $this->hasChild($child)
				? $this->childs[$child->getId()]
				: null;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function addChild(OqlSyntaxNode $child)
		{
			$this->childs[$child->getId()] = $child;
			$child->parent = $this;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropChild(OqlSyntaxNode $child)
		{
			if ($this->hasChild($child)) {
				unset($this->childs[$child->getId()]);
				$child->parent = null;
			}
			
			return $this;
		}
		
		public function getChilds()
		{
			return $this->childs;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function setChilds(array $childs)
		{
			$this->dropChilds();
			foreach ($childs as $child)
				$this->addChild($child);
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropChilds()
		{
			foreach ($this->childs as $child)
				$child->dropParent();
			
			return $this;
		}
		
		/**
		 * @return OqlToken
		**/
		public function getToken()
		{
			return $this->token;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function setToken(OqlToken $token)
		{
			$this->token = $token;
			
			return $this;
		}
	}
?>