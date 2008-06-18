<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * View resolver for php templates with multiple prefix support
	 * 
	 * Will resolve view to first readable template from
	 * supplied prefixes list
	 * 
	 * @ingroup Flow
	**/
	class MultiPrefixPhpViewResolver implements ViewResolver
	{
		private $prefixes	= array();
		private $lastAlias	= null;
		
		private $disabled	= array();
		
		private $postfix	= EXT_TPL;
		private $viewClassName	= 'SimplePhpView';
		
		/**
		 * @return MultiPrefixPhpView
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return MultiPrefixPhpView
		**/
		public function addFirstPrefix($prefix)
		{
			array_unshift($this->prefixes, $prefix);
			
			return $this;
		}
		
		/**
		 * @return MultiPrefixPhpView
		**/
		public function addPrefix($prefix, $alias = null)
		{
			if (!$alias)
				$alias = $this->getAutoAlias($prefix);
			
			Assert::isFalse(
				isset($this->prefixes[$alias]),
				'alias already exists'
			);
				
			$this->prefixes[$alias] = $prefix;
			
			$this->lastAlias = $alias;
			
			return $this;
		}
		
		public function getPrefixes()
		{
			return $this->prefixes;
		}
		
		/**
		 * @return MultiPrefixPhpViewResolver
		**/
		public function dropPrefixes()
		{
			$this->prefixes = array();
			return $this;
		}
		
		/**
		 * @return MultiPrefixPhpViewResolver
		**/
		public function disablePrefix($alias = null, $disabled = true)
		{
			if (!$alias)
				$alias = $this->lastAlias;
			
			Assert::isNotNull($alias, 'nothing to disable');
			
			$this->disabled[$alias] = $disabled;
			
			return $this;
		}
		
		public function enablePrefix($alias)
		{
			return $this->disablePrefix($alias, false);
		}
		
		public function getPostfix()
		{
			return $this->postfix;
		}
		
		/**
		 * @return MultiPrefixPhpView
		**/
		public function setPostfix($postfix)
		{
			$this->postfix = $postfix;
			return $this;
		}
		
		/**
		 * @return SimplePhpView
		**/
		public function resolveViewName($viewName)
		{
			Assert::isFalse(
				($this->prefixes === array()),
				'specify at least one prefix'
			);
			
			if ($prefix = $this->findPrefix($viewName))
				return
					new $this->viewClassName(
						$prefix.$viewName.$this->postfix,
						$this
					);
			
			if (!$this->findPrefix($viewName, false))
				throw new WrongArgumentException(
					'can not resolve view: '.$viewName
				);
			
			return EmptyView::create();
		}
		
		public function viewExists($viewName)
		{
			return ($this->findPrefix($viewName) !== null);
		}
		
		/**
		 * @return MultiPrefixPhpView
		**/
		public function setViewClassName($viewClassName)
		{
			$this->viewClassName = $viewClassName;
			
			return $this;
		}
		
		public function getViewClassName()
		{
			return $this->viewClassName;
		}
		
		protected function findPrefix($viewName, $checkDisabled = true)
		{
			foreach ($this->prefixes as $alias => $prefix) {
				if (
					$checkDisabled
					&& isset($this->disabled[$alias])
					&& $this->disabled[$alias]
				)
					continue;
				
				if (file_exists($prefix.$viewName.$this->postfix))
					return $prefix;
			}
			
			return null;
		}
		
		private function getAutoAlias($prefix)
		{
			return md5($prefix);
		}
	}
?>