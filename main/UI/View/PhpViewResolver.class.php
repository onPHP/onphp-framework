<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	class PhpViewResolver implements ViewResolver
	{
		private $prefix		= null;
		private $postfix	= null;
		
		public function __construct($prefix = null, $postfix = null)
		{
			$this->prefix	= $prefix;
			$this->postfix	= $postfix;
		}
		
		/**
		 * @return PhpViewResolver
		**/
		public static function create($prefix = null, $postfix = null)
		{
			return new self($prefix, $postfix);
		}
		
		/**
		 * @param $viewNameList string|string[]
		 * @return SimplePhpView
		 * @throws WrongArgumentException
		**/
		public function resolveViewName($viewNameList)
		{
			foreach ($this->getViewNameList($viewNameList) as $viewName) {
				if ($this->isViewNameExists($viewName)) {
					return new SimplePhpView(
						$this->prefix.$viewName.$this->postfix,
						$this
					);
				}
			}
			throw new WrongArgumentException(
				'can not resolve views: '.implode($this->getViewNameList($viewNameList))
			);
		}
		
		public function viewExists($viewNameList)
		{
			foreach ($this->getViewNameList($viewNameList) as $viewName) {
				if ($this->isViewNameExists($viewName)) {
					return true;
				}
			}
			return false;
		}
		
		public function getPrefix()
		{
			return $this->prefix;
		}
		
		/**
		 * @return PhpViewResolver
		**/
		public function setPrefix($prefix)
		{
			$this->prefix = $prefix;
			
			return $this;
		}
		
		public function getPostfix()
		{
			return $this->postfix;
		}
		
		/**
		 * @return PhpViewResolver
		**/
		public function setPostfix($postfix)
		{
			$this->postfix = $postfix;
			
			return $this;
		}

		private function isViewNameExists($viewName)
		{
			return is_readable($this->prefix.$viewName.$this->postfix);
		}

		private function getViewNameList($viewNameOrList)
		{
			return is_array($viewNameOrList) ? $viewNameOrList : [$viewNameOrList];
		}
	}
?>