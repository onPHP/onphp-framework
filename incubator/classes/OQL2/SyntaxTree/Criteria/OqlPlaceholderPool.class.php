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
	final class OqlPlaceholderPool
	{
		private $pool = array();
		private $keys = array();
		
		/**
		 * @return OqlPlaceholderPool
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlPlaceholder
		**/
		public function spawn($name)
		{
			if (!$this->has($name))
				$this->add(OqlPlaceholder::create($name));
			
			return $this->get($name);
		}
		
		public function has($name)
		{
			return isset($this->keys[$name]);
		}
		
		/**
		 * @throws MissingElementException
		 * @return OqlPlaceholder
		**/
		public function get($name)
		{
			if ($this->has($name))
				return $this->pool[$this->keys[$name]];
			
			throw new MissingElementException(
				"knows nothing about such placeholder '{$name}'"
			);
		}
		
		/**
		 * @return OqlPlaceholder
		**/
		public function getNext($placeholder)
		{
			Assert::isTrue(
				$placeholder === null || $placeholder instanceof OqlPlaceholder
			);
			
			if ($placeholder === null)
				return reset($this->pool);
			
			if ($this->has($placeholder->getName())) {
				$index = $this->keys[$placeholder->getName()];
				
				if (isset($this->pool[$index + 1]))
					return $this->pool[$index + 1];
			}
			
			return null;
		}
		
		public function getList()
		{
			if ($this->keys)
				return array_combine(array_keys($this->keys), $this->pool);
			else
				return array();
		}
		
		/**
		 * @return OqlPlaceholderPool
		**/
		public function add(OqlPlaceholder $placeholder)
		{
			Assert::isFalse(
				$this->has($placeholder->getName()),
				"placeholder named '{$placeholder->getName()}' is already in pool. use spawn to create one."
			);
			
			$this->pool[] = $placeholder;
			$this->keys[$placeholder->getName()] = count($this->pool) - 1;
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return OqlPlaceholder
		**/
		public function drop($name)
		{
			if (!$this->has($name))
				throw new MissingElementException(
					"knows nothing about such placeholder '{$name}'"
				);
			
			unset($this->pool[$this->keys[$name]]);
			unset($this->keys[$name]);
			
			return $this;
		}
	}
?>