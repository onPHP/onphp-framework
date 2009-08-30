<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	final class RedirectToView extends RedirectView
	{
		private $prefix = null;
		private $suffix = null;
		
		/**
		 * @return RedirectToView
		**/
		public static function create($controllerName)
		{
			return new self($controllerName);
		}
		
		public function __construct($controllerName)
		{
			Assert::isTrue(
				class_exists($controllerName, true)
			);
			
			$this->url = $controllerName;
		}
		
		public function getPrefix()
		{
			return $this->prefix;
		}
		
		/**
		 * @return RedirectToView
		**/
		public function setPrefix($prefix)
		{
			$this->prefix = $prefix;
			
			return $this;
		}
		
		public function getSuffix()
		{
			return $this->suffix;
		}
		
		/**
		 * @return RedirectToView
		**/
		public function setSuffix($suffix)
		{
			$this->suffix = $suffix;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->url;
		}
		
		/**
		 * @return RedirectToView
		**/
		public function setName($name)
		{
			$this->url = $name;
			
			return $this;
		}
		
		public function getUrl()
		{
			return $this->prefix.$this->url.$this->suffix;
		}
	}
?>