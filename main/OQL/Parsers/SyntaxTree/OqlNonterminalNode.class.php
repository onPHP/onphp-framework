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
	class OqlNonterminalNode extends OqlSyntaxNode
	{
		protected $childs = array();
		
		/**
		 * @return OqlNonterminalNode
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __destruct()
		{
			$this->dropChilds();
			if (isset($this->parent))
				$this->parent->dropChild($this);
		}
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function setParent(OqlSyntaxNode $parent)
		{
			Assert::isTrue($parent instanceof OqlNonterminalNode);
			
			if ($this->parent !== $parent) {
				if ($this->parent)
					$this->parent->dropChild($this);
				
				parent::setParent($parent);
				
				$this->parent->addChild($this);
			}
			
			return $this;
		}
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function dropParent()
		{
			if ($this->parent) {
				$this->parent->dropChild($this);
				
				parent::dropParent();
			}
			
			return $this;
		}
		
		public function hasChild(OqlSyntaxNode $child)
		{
			return isset($this->childs[$child->getId()]);
		}
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function addChild(OqlSyntaxNode $child)
		{
			$this->childs[$child->getId()] = $child;
			$child->parent = $this;
			
			return $this;
		}
		
		/**
		 * @return OqlNonterminalNode
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
		 * @return OqlNonterminalNode
		**/
		public function setChilds(array $childs)
		{
			$this->dropChilds();
			foreach ($childs as $child)
				$this->addChild($child);
			
			return $this;
		}
		
		/**
		 * @return OqlNonterminalNode
		**/
		public function dropChilds()
		{
			foreach ($this->childs as $child)
				$child->dropParent();
			
			return $this;
		}
		
		public function toString()
		{
			if ($this->childs) {
				$result = '{';
				
				foreach ($this->childs as $child) {
					$result .= $child->toString();
				}
				
				$result .= '}';
				
				return $result;
			}
			
			return null;
		}
	}
?>