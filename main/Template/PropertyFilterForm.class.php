<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * PropertyFilter contains Form as main property
	**/
	class PropertyFilterForm extends PropertyFilter
	{
		// properties
		/**
		 * Form object.
		 * 
		 * @todo 	what if document will contain several forms?
		**/
		private $_form = null;
		
		/**
		 * full qualified url to main script
		**/
		private $_url = null;
		
		/**
		 * area flag
		**/
		private $_area = null;
		
		/**
		 * Sets value of $_form
		 * 
		 * @return	void
		**/
		public function setForm(Form $form)
		{
			$this->_form = $form;
			
			return $this;
		}
		
		/**
		 * Returns form property
		 * 
		 * @return	Form
		**/
		public function getForm()
		{
			return $this->_form;
		}
		
		/**
		 * Returns Form Primitive as string
		 * 
		 * @return	string	Form Primitive as string
		**/
		public function getRawValue($primitiveName)
		{
			if ($this->_form) {
				try {
					return $this->_form->getRawValue($primitiveName);
				} catch (ObjectNotFoundException $e) {
					return null;
				}
			}
			
			return null;
		}
		
		/**
		 * Returns Form Primitive as filtered string
		 * 
		 * @return	string	Primitive as string handled by default handler
		*/
		public function getValue($primitiveName)
		{
			try {
				if ($value = $this->getRawValue($primitiveName))
					try {
						$method = $this->getDefaultHandler();
						return $this->$method($this->getRawValue($primitiveName));
					} catch (BaseException $e) {
						return $this->getDefault();
					}
			} catch (BaseException $e) {
				return $this->getDefault();
			}
		}
		
		/**
		 * Sets url
		 * 
		 * @return	void
		**/
		public function setUrl($url)
		{
			$this->_url	= $url;
			
			return $this;
		}
		
		/**
		 * A bit odd: sets main url and area flag
		 * 
		 * @return	void
		**/
		public function setArea($url, $area)
		{
			$this->_url	= $url;
			$this->_area	= $area;
			
			return $this;
		}
		
		/**
		 * Returns full path to specified area
		 * 
		 * @return	string	full path to specified by $name parameter area
		**/
		public function getArea($name)
		{
			return "{$this->_url}?{$this->_area}={$name}";
		}
	}
?>