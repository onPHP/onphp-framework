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
 /* $Id$ */

	/**
	 * Features are not in Dummy:
	 * 1. Keeps output info about templates
	 * 2. Outputs templates
	 *
	 * @author	Sveta Smirnova <sveta@microbecal.com>
	 * @access	public
	 * @version	1.0.0
	 * @since	PHP 5.0.0
	 * @package	Template
	 * @category Template
	**/

	class PropertyFilter extends Dummy
	{
		/**
		 * Default template's extention
		**/
		const DEFAULT_EXTENTION = EXT_TPL;
		
		/**
		 * Suffix for array_walk handlers
		**/
		const AW_SUFFIX			= '_aw';
		
		/**
		 * @var		PropertyFilterDefaultPP contains public property with special behavior
         * 									Important! Made to be public for out
         *                                  free access: mock in some situation.
         *                                  Use carefull.
		 * @access	public
		**/
		public $publicProperty = null;
		
		/**
		 * Contains PropertyFilter instance
		**/
		private static $instance = null;
		
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
		private $extension = self::DEFAULT_EXTENTION;
		
		/**
		 * @var		string	default output handler
		 * @access	private
		**/
		private $defaultHandler = null;
		
		/**
		 * @var		string	default output array_walk handler
		 * @access	private
		**/
		private $defaultAWHandler = null;
		
		/**
		 * @var		array	default output handler parameters
		 * @access	private
		**/
		private $defaultParams = null;

		/**
		 * Creates PropertyFilter
		 *
		 * @todo	Refactor it!!!
		 * @param	string		default handler
		 * @return	PropertyFilter		PropertyFilter instance
		 * @access	public
		**/
		public static function getInstance($handler = 'nullHandler')
		{
			// TODO: why not make it thru standart Singletone?
			// No, we should extend Dummy which I don't want to make Singltone
            // Of course, I can imitate multiple inheritance, but not now :-/
			if (null == PropertyFilter::$instance) {
				PropertyFilter::$instance = new PropertyFilter;
			}

			$args = func_get_args();
			PropertyFilter::$instance->setDefaultHandler($handler, array_slice($args, 1));

			return PropertyFilter::$instance;
		}
	
		/**
		 * Returns value of PropertyFilter::variable_name
		 * hanled by default handler
		 * 
		 * @param	string	$variable_name	variable name
		 * @return	mixed					value of variable_name
		 *									if variable_name was set
		 *									default value otherwise
		 * @access	public
		**/
		public function __get($var)
		{
			// start dangerous code
            /*
			$bt = debug_backtrace();
			if (__CLASS__ == $bt[0]['class']) {
				return parent::__get($var);
			}
			*/
			// end dangerous code

			$params = $this->getDefaultParams();

			if (isset($this->variables[$var])) {
				if (is_scalar($this->variables[$var])) {
					array_splice($params, 0, 0, array($this->variables[$var]));
					return call_user_func_array(array($this, $this->getDefaultHandler()), $params);
				} elseif (is_array($this->variables[$var])) {
					// array_walk_recursive through array variable
					$result = $this->variables[$var];
					array_walk_recursive($result, array($this, $this->getDefaultAWHandler()), $params);
					return $result;
				} else {
					// array_walk_recursive through array variable
					return $this->variables[$var];
				}
			} else {
				array_splice($params, 0, 0, array($this->getDefault()));
				return call_user_func_array(array($this, $this->getDefaultHandler()), $params);
			}
		}
		
		public function getRaw($var)
		{
			return parent::__get($var);
		}
	
		/**
		 * Set extension for templates
		 *
		 * @param	string	$ext	new extension
		 * @return	void
		 * @access	public
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
		 * @access	public
		**/
		public function getExtension()
		{
			return $this->extension;
		}
	
		/**
		 * Sets templates directory
		 *
		 * @param	string	$dir	new templates directory
		 * @return	void
		 * @access	public
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
		 * @access	public
		**/
		public function getTemplateDir()
		{
			return $this->templateDir;
		}
	
		/**
		 * Sets templates array
		 *
		 * @param	string	$template	list of strings: files names without extensions
		 * @return	void
		 * @access	public
		**/
		public function setTemplates($template)
		{
			$this->templates = func_get_args();
			
			return $this;
		}
	
		/**
		 * Sets templates array
		 *
		 * @param	mixed	$templates	array contains templates files names
         * 								without extensions
		 * @return	void
		 * @access	public
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
		 * @access	public
		**/
		public function getTemplates()
		{
			return $this->templates;
		}
	
		/**
		 * Displays all templates into stdout
		 *
		 * @param	string	template name
		 * @return	void
		 * @access	public
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
						include $this->templateDir . $template . $this->extension;
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
		 * @todo		is_array($haystack)
		 * @todo		is_string(each of $haystack keys)
		 * @param	array	$haystack	haystack
		 * @return	void
		 * @access	public
		**/
		public function fill($haystack)
		{
			foreach ($haystack as $key => $value) {
					$this->{$key} = $value;
			}
		}

		/**
		 * Sets default output handler
		 * 
		 * @param	string	$handler	handler name
		 * @param	array	$params		default params
		 * @return	void
		 * @access	public
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
		 * @access	public
		**/
		public function getDefaultHandler()
		{
			return $this->defaultHandler;
		}
	
		/**
		 * Returns default output params
		 * 
		 * @return	array	default output params
		 * @access	public
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
	
	/**
	 * @todo insertTemplate
	 * @todo insertTemplates
	 * @todo removeTemplate
	 * @todo removeTemplates
	 * @todo getTemplates
	**/
	
		protected function __construct(/*$publicProperty = null*/)
		{
			$this->publicProperty = new PropertyFilterDefaultPublicProperty();
		}

		/**
		 * Returns default output handler for array_walk
		 * 
		 * @return	string	default output handler name
		 * @access	private
		**/
		private function getDefaultAWHandler()
		{
			return $this->defaultAWHandler;
		}

		// TODO: rename it according to youKnowWhat ;-)
		// no ;-/
		public function htmlspecialchars_aw(&$string, $key, $params = null)
		{
			if (is_string($string)) {
				$quote_style = isset($params[0]) ? $params[0] : ENT_COMPAT;
				$charset = isset($params[1]) ? $params[1] :'cp1251';
				$string = htmlspecialchars($string, $quote_style, $charset);
			}
		}

		// TODO: rename it according to youKnowWhat ;-)
		// no ;-/
		public function nullHandler_aw(&$string, $key, $params = null)
		{
			//	do nothing
		}
	}
?>