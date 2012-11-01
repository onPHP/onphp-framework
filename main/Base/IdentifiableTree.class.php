<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @see NamedTree
	 * 
	 * @ingroup Helpers
	**/
	namespace Onphp;

	abstract class IdentifiableTree
		extends IdentifiableObject
		implements Stringable
	{
		private $parent	= null;
		
		/**
		 * @return \Onphp\IdentifiableTree
		**/
		public function getParent()
		{
			return $this->parent;
		}
		
		/**
		 * @return \Onphp\IdentifiableTree
		**/
		public function setParent(IdentifiableTree $parent)
		{
			Assert::brothers($this, $parent);
			
			$this->parent = $parent;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\IdentifiableTree
		**/
		public function dropParent()
		{
			$this->parent = null;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\IdentifiableTree
		**/
		public function getRoot()
		{
			$current = $this;
			$next = $this;
			
			while ($next) {
				$current = $next;
				$next = $next->getParent();
			}
			
			return $current;
		}
		
		public function toString($delimiter = ', ')
		{
			$ids = array($this->getId());
			
			$parent = $this;
			
			while ($parent = $parent->getParent())
				$ids[] = $parent->getId();
			
			$ids = array_reverse($ids);
			
			return implode($delimiter, $ids);
		}
	}
?>