<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
		private $postfix	= null;
		
		/**
		 * @return MultiPrefixPhpView
		 */
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return MultiPrefixPhpView
		 */
		public function addPrefix($prefix)
		{
			$this->prefixes[] = $prefix;
			return $this;
		}
		
		public function getPrefixes()
		{
			return $this->prefixes;
		}
		
		/**
		 * @return MultiPrefixPhpView
		 */
		public function dropPrefixes()
		{
			$this->prefixes = array();
			return $this;
		}
		
		public function getPostfix()
		{
			return $this->postfix;
		}
		
		/**
		 * @return MultiPrefixPhpView
		 */
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
			Assert::isTrue(
				count($this->prefixes) >= 1, 
				'specify at least one prefix'
			);
			
			foreach ($this->prefixes as $prefix) {
				if (is_readable($prefix.$viewName.$this->postfix)) {
					return
						new SimplePhpView(
							$prefix.$viewName.$this->postfix,
							$this
						);
				}
			}

			throw new ObjectNotFoundException('can\'t resolve view:'.$viewName);
		}
	}
?>