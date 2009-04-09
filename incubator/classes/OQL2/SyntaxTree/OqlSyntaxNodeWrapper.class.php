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
	abstract class OqlSyntaxNodeWrapper extends OqlSyntaxNode
	{
		protected $node = null;
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getNode()
		{
			return $this->node;
		}
		
		/**
		 * @return OqlSyntaxNodeWrapper
		**/
		public function setNode(OqlSyntaxNode $node)
		{
			$this->node = $node;
			$this->parent = $node->getParent();
			
			return $this;
		}
		
		public function getId()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getId();
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
			Assert::isNotNull($this->node);
			
			$this->node->setParent($parent);
			$this->parent = $parent;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropParent()
		{
			Assert::isNotNull($this->node);
			
			$this->node->dropParent();
			$this->parent = null;
			
			return $this;
		}
		
		public function hasChild(OqlSyntaxNode $child)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->hasChild($child);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function addChild(OqlSyntaxNode $child)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->addChild($child);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropChild(OqlSyntaxNode $child)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->dropChild($child);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getFirstChild()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getFirstChild();
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getLastChild()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getLastChild();
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getNextChild(OqlSyntaxNode $child)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getNextChild($child);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function getPrevChild(OqlSyntaxNode $child)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getPrevChild($child);
		}
		
		public function getChilds()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->getChilds();
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function setChilds(array $childs)
		{
			Assert::isNotNull($this->node);
			
			return $this->node->setChilds($childs);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function dropChilds()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->dropChilds();
		}
		
		public function toValue()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->toValue();
		}
		
		public function toString()
		{
			Assert::isNotNull($this->node);
			
			return $this->node->toString();
		}
	}
?>