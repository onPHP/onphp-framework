<?php
/***************************************************************************
 *	 Copyright (C) 2004-2005 by Sveta Smirnova							   *
 *	 sveta@microbecal.com												   *
 *																		   *
 *	 This program is free software; you can redistribute it and/or modify  *
 *	 it under the terms of the GNU General Public License as published by  *
 *	 the Free Software Foundation; either version 2 of the License, or	   *
 *	 (at your option) any later version.								   *
 *																		   *
 ***************************************************************************/
 /* $Id$*/
 
	/**
	 * PropertyFilter contains Form as main property
	 * 
	 * @package		Template
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/
	class PropertyFilterForm extends PropertyFilter
	{
		// properties
		/**
		 * @todo 	what if document will contain several forms?
		 * @var		Form	Form object
		 * @access	private
		**/
		private $_form = null;
		
		/**
		 * @var		string	full qualified url to main script
		 * @access	private
		**/
		private $_url = null;
		
		/**
		 * @var		string	area flag
		 * @access	private
		**/
		private $_area = null;
		
		/**
		 * Creates PropertyFilterForm
		 *
		 * @todo	rewrite to use standart Singletone class ;-)
		 * @param	string		default handler
		 * @return	PropertyFilterForm PropertyFilterForm instance
		 * @access	public
		**/
		public static function getInstance($handler = 'nullHandler')
		{
			static $_instance = null;
			if (null == $_instance) {
				$_instance = new PropertyFilterForm;
			}
			$args = func_get_args();
			$_instance->setDefaultHandler($handler, array_slice($args, 1));
			return $_instance;
		}
		
		/**
		 * Sets value of $_form
		 * 
		 * @param	Form	ready Form
		 * @access	public
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
		 * @access	public
		 * @return	Form
		**/
		public function getForm()
		{
			return $this->_form;
		}
		
		/**
		 * Returns Form Primitive as string
		 * 
		 * @param	string	Primitive name
		 * @access	public
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
		 * @param	string	Primitive name
		 * @access	public
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
		 * @param	string	url
		 * @access	public
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
		 * @param	string	url
		 * @param	string	area flag
		 * @access	public
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
		 * @param	string	area name
		 * @access	public
		 * @return	string	full path to specified by $name parameter area
		**/
		public function getArea($name)
		{
			return "{$this->_url}?{$this->_area}={$name}";
		}
	}
?>
