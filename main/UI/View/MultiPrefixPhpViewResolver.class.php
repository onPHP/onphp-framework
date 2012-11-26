<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * View resolver for php templates with multiple prefix support
	 * 
	 * Will resolve view to first readable template from
	 * supplied prefixes list
	 * 
	 * @ingroup Flow
	**/
	namespace Onphp;

	class MultiPrefixPhpViewResolver implements ViewResolver
	{
		private $prefixes	= array();
		private $lastAlias	= null;
		
		private $disabled	= array();
		
		private $postfix	= EXT_TPL;
		private $viewClassName	= '\Onphp\SimplePhpView';
		private $nameAliases = array();
		
		/**
		 * @return \Onphp\MultiPrefixPhpViewResolver
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\MultiPrefixPhpViewResolver
		**/
		public function addFirstPrefix($prefix)
		{
			array_unshift($this->prefixes, $prefix);
			
			return $this;
		}
		
		/**
		 * @return \Onphp\MultiPrefixPhpViewResolver
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
		 * @return \Onphp\MultiPrefixPhpViewResolver
		**/
		public function dropPrefixes()
		{
			$this->prefixes = array();
			return $this;
		}
		
		public function isPrefixDisabled($alias)
		{
			Assert::isIndexExists(
				$this->prefixes,
				$alias,
				'no such alias: '.$alias
			);
			
			return !empty($this->disabled[$alias]);
		}
		
		/**
		 * @return \Onphp\MultiPrefixPhpViewResolver
		**/
		public function disablePrefix($alias = null, $disabled = true)
		{
			if (!$alias)
				$alias = $this->lastAlias;
			
			Assert::isNotNull($alias, 'nothing to disable');
			Assert::isIndexExists(
				$this->prefixes,
				$alias,
				'no such alias: '.$alias
			);
			
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
		 * @return \Onphp\MultiPrefixPhpViewResolver
		**/
		public function setPostfix($postfix)
		{
			$this->postfix = $postfix;
			return $this;
		}
		
		/**
		 * @param string $nameAlias
		 * @return \Onphp\MultiPrefixPhpViewResolver
		 */
		public function addNameAlias($viewNameFrom, $viewNameTo)
		{
			$this->nameAliases[$viewNameFrom] = $viewNameTo;
			return $this;
		}

		/**
		 * @return \Onphp\SimplePhpView
		**/
		public function resolveViewName($viewName)
		{
			Assert::isFalse(
				($this->prefixes === array()),
				'specify at least one prefix'
			);
			
			if ($path = $this->findPath($viewName))
				return $this->makeView($path);
			
			if (!$this->findPath($viewName, false))
				throw new WrongArgumentException(
					'can not resolve view: '.$viewName
				);
			
			return EmptyView::create();
		}
		
		public function viewExists($viewName)
		{
			return ($this->findPath($viewName) !== null);
		}
		
		/**
		 * @return \Onphp\MultiPrefixPhpViewResolver
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
		
		protected function findPath($viewName, $checkDisabled = true)
		{
			foreach ($this->prefixes as $alias => $prefix) {
				if (
					$checkDisabled
					&& isset($this->disabled[$alias])
					&& $this->disabled[$alias]
				)
					continue;
				
				if (file_exists($prefix.$viewName.$this->postfix))
					return $prefix.$viewName.$this->postfix;

				$aliasedViewName = $viewName;
				while (isset($this->nameAliases[$aliasedViewName])) {
					$aliasedViewName = $this->nameAliases[$aliasedViewName];
					if (file_exists($prefix.$aliasedViewName.$this->postfix))
						return $prefix.$aliasedViewName.$this->postfix;
				}
			}
			
			return null;
		}
		
		/**
		 * @return \Onphp\View
		**/
		protected function makeView($path)
		{
			return new $this->viewClassName($path, $this);
		}
		
		private function getAutoAlias($prefix)
		{
			return md5($prefix);
		}
	}
?>