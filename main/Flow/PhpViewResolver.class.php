<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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

		public static function create($prefix = null, $postfix = null)
		{
			return new self($prefix, $postfix);
		}
		
		public function resolveViewName($viewName)
		{
			return
				new SimplePhpView(
					$this->prefix.$viewName.$this->postfix,
					$this
				);
		}
		
		public function viewExists($viewName)
		{
			return is_readable($this->prefix.$viewName.$this->postfix);
		}
		
		public function getPrefix()
		{
			return $this->prefix;
		}
		
		public function setPrefix($prefix)
		{
			$this->prefix = $prefix;
			
			return $this;
		}
		
		public function getPostfix()
		{
			return $this->postfix;
		}
		
		public function setPostfix($postfix)
		{
			$this->postfix = $postfix;
			
			return $this;
		}
	}
?>