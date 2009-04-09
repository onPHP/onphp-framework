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
	class OqlBindableNodeWrapper extends OqlSyntaxNodeWrapper
	{
		protected $pool		= null;
		private $lastBinded	= null; 
		
		public function __construct()
		{
			parent::__construct();
			$this->pool = OqlPlaceholderPool::create();
		}
		
		/**
		 * @return OqlPlaceholderPool
		**/
		public function getPool()
		{
			return $this->pool;
		}
		
		/**
		 * @return OqlBindableNodeWrapper
		**/
		public function bind($name, $value)
		{
			if ($this->pool->has($name))
				$this->bindPlaceholder($this->pool->get($name), $value);
			
			return $this;
		}
		
		/**
		 * @return OqlBindableNodeWrapper
		**/
		public function bindNext($value)
		{
			if ($placeholder = $this->pool->getNext($this->lastBinded))
				$this->bindPlaceholder($placeholder, $value);
			
			return $this;
		}
		
		/**
		 * @return OqlBindableNodeWrapper
		**/
		public function bindAll(array $parameters)
		{
			foreach ($this->pool->getList() as $placeholder) {
				if (isset($parameters[$placeholder->getName()])) {
					$this->bindPlaceholder(
						$placeholder,
						$parameters[$placeholder->getName()]
					);
				}
			}
			
			return $this;
		}
		
		/**
		 * @return OqlBindableNodeWrapper
		**/
		private function bindPlaceholder(OqlPlaceholder $placeholder, $value)
		{
			$placeholder->bind($value);
			$this->lastBinded = $placeholder;
			
			return $this;
		}
	}
?>