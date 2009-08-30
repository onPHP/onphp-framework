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

	class PropertyFilter extends Singleton /*, DummyTemplate*/
	{
		/**
		 * Default template's extention
		**/
		const DEFAULT_EXTENTION = EXT_TPL;
		
		/**
		 * Suffix for array_walk handlers
		**/
		const AW_SUFFIX			= 'AW';
		
		/**
		 * DummyTemplate instance
		**/
		private $dummyTemplate = null;
		
		/**
		 * Contains public property with special behavior.
         * Important! Made to be public for out free access:
         * mock in some situation.
         * Use carefull.
		**/
		public $publicProperty = null;
		
		/**
		 * Contains templates
		**/
		private $templates = array();
		
		/**
		 * Directory with templates
		**/
		private $templateDir = null;
		
		/**
		 * Templates extension
		**/
		private $extension = PropertyFilter::DEFAULT_EXTENTION;
		
		/**
		 * default output handler
		**/
		private $defaultHandler = null;
		
		/**
		 * default output array_walk handler
		**/
		private $defaultAWHandler = null;
		
		/**
		 * default output handler parameters
		**/
		private $defaultParams = null;

		public function getRaw($var)
		{
			return parent::__get($var);
		}
	
		/**
		 * Set extension for templates
		 * 
		 * @return	void
		**/
		public function setExtension($extension)
		{
			$this->extension = $extension;
			
			return $this;
		}
	
		/**
		 * Returns templates extension
		 * 
		 * @return	string	templates extension
		**/
		public function getExtension()
		{
			return $this->extension;
		}
	
		/**
		 * Sets templates directory
		 * 
		 * @return	void
		**/
		public function setTemplateDir($dir)
		{
			$this->templateDir = $dir;

			return $this;
		}
	
		/**
		 * Returns templates directory
		 * 
		 * @return	string	templates directory
		**/
		public function getTemplateDir()
		{
			return $this->templateDir;
		}
	
		/**
		 * Sets templates array as list of file names
		 * 
		 * @return	void
		**/
		public function setTemplates(/* ... */)
		{
			$this->templates = func_get_args();
			
			return $this;
		}
	
		/**
		 * Sets templates array
		 * 
		 * @param	$templates	array contains templates files names
         * 						without extensions
		 * @return	void
		**/
		public function setTemplatesArray($templates)
		{
			$this->templates = $templates;
			
			return $this;
		}
	
		/**
		 * Returns array of templates names
		 * 
		 * @return	array
		**/
		public function getTemplates()
		{
			return $this->templates;
		}
	
		/**
		 * Displays all templates into stdout
		 * 
		 * @param	$template	template name
		 * @return	void
		**/
		public function display($template = null)
		{
			if (null !== $template) {
				try {
					if (file_exists($needed = $this->templateDir . DIRECTORY_SEPARATOR . $template . $this->extension)) {
						include $needed;
					}
				} catch (BaseException $e) {
					// do nothing
				}
			} elseif ($this->templates) {
				foreach ($this->templates as $template) {
					try {
						include $this->templateDir . DIRECTORY_SEPARATOR . $template . $this->extension;
					} catch (BaseException $e) {
						// do nothing
					}
				}
			}
		}

		/**
		 * Fills template container by values from
		 * haystack
		 * 
		 * @todo	is_array($haystack)
		 * @todo	is_string(each of $haystack keys)
		 * 
		 * @return	void
		**/
		public function fill($haystack)
		{
			foreach ($haystack as $key => $value) {
					$this->{$key} = $value;
			}
			
			return $this;
		}

		/**
		 * Sets default output handler
		 * 
		 * @param	$handler	string	handler name
		 * @param	$params		array	default params
		 * 
		 * @return	void
		**/
		public function setDefaultHandler($handler, $params = array())
		{
			$this->defaultHandler	= $handler;
			$this->defaultAWHandler	= $handler . PropertyFilter::AW_SUFFIX;

			$this->defaultParams	= $params;
			
			return $this;
		}

		/**
		 * Returns default output handler
		 * 
		 * @return	string	default output handler name
		**/
		public function getDefaultHandler()
		{
			return $this->defaultHandler;
		}
	
		/**
		 * Returns default output params
		 * 
		 * @return	array	default output params
		**/
		public function getDefaultParams()
		{
			return $this->defaultParams;
		}
		
		// TODO: why duplicate it? or rename it according to youKnowWhat ;-/
		// you know =/
		public function htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'utf-8')
		{
			return htmlspecialchars($string, $quote_style, $charset);
		}
	
		public function nullHandler($string)
		{
			return $string;
		}
		
		public function __call($method, $params)
		{
			if (method_exists($this->dummyTemplate, $method)) {
				return call_user_func_array(array($this->dummyTemplate, $method), $params);
			} else {
				return $this->dummyTemplate->__call($method, $params);
			}
		}
		
		/**
		 * calls Dummy::__set()
		 * 
		 * @return	void
		**/
		public function __set($variableName, $value)
		{
			return $this->dummyTemplate->__set($variableName, $value);
		}
		
		/**
		 * calls Dummy::__get()
		 * 
		 * @return	void
		**/
		public function __get($variableName)
		{
			return $this->dummyTemplate->__get($variableName);
		}

		/**
		 * @todo insertTemplate
		 * @todo insertTemplates
		 * @todo removeTemplate
		 * @todo removeTemplates
		 * @todo getTemplates
		**/
	
		protected function __construct()
		{
			$args = func_get_arg(1);
			if (1 <= count($args)) {
				$handler = array_shift($args);
			} else {
				$handler = 'nullHandler';
			}
			$this->setDefaultHandler($handler, $args);
			$this->publicProperty = new PropertyFilterDefaultPublicProperty();
			$this->dummyTemplate  = new DummyTemplateWrapper($this);
		}

		/**
		 * Returns default output handler for array_walk
		 * 
		 * @return	string	default output handler name
		**/
		public function getDefaultAWHandler()
		{
			return $this->defaultAWHandler;
		}

		public function htmlspecialcharsAW(&$string, $key, $params = null)
		{
			if (is_string($string)) {
				$quote_style = isset($params[0]) ? $params[0] : ENT_COMPAT;
				$charset = isset($params[1]) ? $params[1] :'cp1251';
				$string = htmlspecialchars($string, $quote_style, $charset);
			}
		}

		public function nullHandlerAW(&$string, $key, $params = null)
		{
			//	do nothing
		}
	}
?>