<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Abstract controller
	**/
	class Controller
	{
		const NOT_SUPPORTED_GLOBAL = 'Not supported global array';
		
		/**
		 * @var	 CommonModule	current active module
		 * @access  private
		**/
		protected $module;
		
		/**
		 * @var	 string	  current template file basename
		 * @access  private
		**/
		protected $template;
		
		/**
		 * @var	 mixed	   template engine instance
		 * @access  private
		**/
		protected $templateEngine;
		
		/**
		 * @var	 boolean	 flag, showed using of environment variable for
		 *					  action selecting
		 * @access  private
		**/
		private $globals = array('_GET', '_POST', '_SESSION', '_COOKIE');
		
		/**
		 * @var	 string
		 * @access  name of action key
		**/
		private $action	 = 'action';
		
		/**
		 * @var	 mixed   handler of module loading callable by call_user_func
		 * @access  private
		**/
		private $moduleHandler = null;
		
		/**
		 * @var	 mixed   handler for template choice callable by call_user_func
		 * @access  private
		**/
		private $templateHandler = null;
		
		/**
		 * @var	 string	  full path to directory with modules
		 * @access  private
		**/
		protected $moduleDir = null;
		
		/**
		 * @var	 string	  full path to directory with templates
		 * @access  private
		**/
		protected $templateDir = null;
		
		/**
		 * @var	 string  extension of module
		 * @access  private
		**/
		protected $moduleExt = '.inc.php';
		
		/**
		 * @var	 string  extension of module
		 * @access  private
		**/
		protected $templateExt = '.tpl.html';
		
		/**
		 * @var	 string	  name of file contains default module name
		 * @access  private
		**/
		protected $default = 'default';
		
		/**
		 * @var		array		array of parametres which should be assigned to template
		 * @access	private
		**/
		protected $parameters		= array();
	
		public static function create()
		{
			return new Controller;
		}
		
		/**
		 * Sets flag of using global
		 * 
		 * @param   string	  global array name
		 * @param   integer	 position in array of globals (starts from 0)
		 * @access  public
		 * @return  self
		**/
		public function useGlobal($global, $position = null)
		{
			if (false !== array_search($global, $this->globals, true)) {
				$this->dontUseGlobal($global);
			}
			if (null === $position || count($this->globals) - 1 < $position) {
				$this->globals[] = $global;
			} else {
				 array_splice($this->globals, $position, 0, $global);
				 $this->globals = array_values($this->globals);
			}
			
			return $this;
		}
		
		/**
		 * Remove array from queue
		 * 
		 * @param   string	  global array name
		 * @access  public
		 * @return  self
		**/
		public function dontUseGlobal($global)
		{
			if (false !== $index = array_search($global, $this->globals, true)) {
				unset($this->globals[$index]);
				$this->globals = array_values($this->globals);
			}
			
			return $this;
		}
		
		public function getGlobals()
		{
			return $this->globals;
		}
		
		/**
		 * Sets function name of module handler, default bundled
		 * 
		 * @param   mixed   array or string callable of call_user_func
		 * @access  public
		 * @return  self
		**/
		public function setModuleHandler($handler)
		{
			$this->moduleHandler = $handler;
			
			return $this;
		}
		
		/**
		 * Returns function name of module handler, default bundled
		 * 
		 * @access  public
		 * @return  mixed   array or string callable of call_user_func
		**/
		public function getModuleHandler()
		{
			return $this->moduleHandler;
		}
		
		/**
		 * Sets function name of template handler, default bundled
		 * 
		 * @param   mixed   array or string callable of call_user_func
		 * @access  public
		 * @return  self
		**/
		public function setTemplateHandler($handler)
		{
			$this->templateHandler = $handler;
			
			return $this;
		}
		
		/**
		 * Returns function name of template handler, default bundled
		 * 
		 * @access  public
		 * @return  mixed   array or string callable of call_user_func
		**/
		public function getTemplateHandler()
		{
			return $this->templateHandler;
		}
		
		/**
		 * Sets path modules directory
		 * 
		 * @param   string	  full path to modules directory
		 * @access  public
		 * @return  self
		**/
		public function setModuleDir($dir)
		{
			$this->moduleDir = $dir;
			
			return $this;
		}
		
		/**
		 * Returns full path to modules directtory
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getModuleDir()
		{
			return $this->moduleDir;
		}
		
		/**
		 * Sets path templates directory
		 * 
		 * @param   string	  full path to templates directory
		 * @access  public
		 * @return  self
		**/
		public function setTemplateDir($dir)
		{
			$this->templateDir = $dir;
			
			return $this;
		}
		
		/**
		 * Returns full path to templates directtory
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getTemplateDir()
		{
			return $this->templateDir;
		}
		
		/**
		 * Sets modules extension
		 * 
		 * @param   string	  new modules extension
		 * @access  public
		 * @return  self
		**/
		public function setModuleExt($ext)
		{
			$this->moduleExt = $ext;
			
			return $this;
		}
		
		/**
		 * Returns modules extension
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getModuleExt()
		{
			return $this->moduleExt;
		}
		
		/**
		 * Sets templates extension
		 * 
		 * @param   string	  new templates extension
		 * @access  public
		 * @return  self
		**/
		public function setTemplateExt($ext)
		{
			$this->templateExt = $ext;
			
			return $this;
		}
		
		/**
		 * Returns full path to templates extension
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getTemplateExt()
		{
			return $this->templateExt;
		}
		
		/**
		 * Sets template engine instance
		 * 
		 * @param   mixed	   new template engine instance
		 * @access  public
		 * @return  self
		**/
		public function setTemplateEngine($templateEngine)
		{
			$this->templateEngine = $templateEngine;
			
			return $this;
		}
		
		/**
		 * Returns template engine instance
		 * 
		 * @access  public
		 * @return  mixed
		**/
		public function getTemplateEngine()
		{
			return $this->templateEngine;
		}
		
		/**
		 * Sets action env flag
		 * 
		 * @param   string	  new action env flag
		 * @access  public
		 * @return  self
		**/
		public function setAction($action)
		{
			$this->action = $action;
			
			return $this;
		}
		
		/**
		 * Returns action env flag
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getAction()
		{
			return $this->action;
		}
		
		/**
		 * Sets file with default module name
		 * 
		 * @param   string	  new file with default module name
		 * @access  public
		 * @return  self
		**/
		public function setDefault($default)
		{
			$this->default = $default;
			
			return $this;
		}
		
		/**
		 * Returns file with default module name
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getDefault()
		{
			return $this->default;
		}
		
		public function setParameters($parameters)
		{
			$this->parameters = $parameters;
		
			return $this;
		}
	
		public function getParameters()
		{
			return $this->parameters;
		}
	
		/**
		 * Returns loaded module
		 * 
		 * @access  public
		 * @return  CommonModule
		**/
		public function getModule()
		{
			return $this->module;
		}
		
		/**
		 * Returns current template name
		 * 
		 * @access  public
		 * @return  string
		**/
		public function getTemplate()
		{
			return $this->template;
		}
		
		/**
		 * Main module handler
		 * 
		 * @access  public
		 * @return  self
		**/
		public function doModel()
		{
			$this->moduleHandler();
			return $this->setParameters(
							 $this->module->init()
											->process()
											->getParameters()
							);
		}
		
		/**
		 * Main view handler
		 * 
		 * @access  public
		 * @return  self
		**/
		public function doView()
		{
			$this->templateHandler();
			$this->templateEngine->
				   setTemplateDir($this->templateDir)->
				   setExtension($this->templateExt)->
				   fill($this->getParameters())->
				   display($this->template);
			
			return $this;
		}
		
		/**
		 * Empty awhile
		 * 
		 * @access  public
		**/
		public function __construct()
		{
			$this->setModuleHandler(array($this, 'moduleHandler'));
			$this->setTemplateHandler(array($this, 'templateHandler'));
		}
		
		/**
		 * Default module loader
		 * 
		 * 
		 * @access  protected
		 * @return  self
		**/
		protected function moduleHandler()
		{
			if ($action = $this->getCurrentAction()) {
				while (true) {
					$needed = $this->moduleDir . DIRECTORY_SEPARATOR .
							implode(DIRECTORY_SEPARATOR, $action);
					if (is_file($file = $needed . $this->moduleExt)) {
						require_once $file;
						$module = array_pop($action);
						$this->module = new $module;
						break;
					} elseif (is_dir($needed)) {
						return $this->loadDefaultModule(implode(DIRECTORY_SEPARATOR, $action));
					} elseif ($action) {
						array_pop($action);
					} else {
						return $this->loadDefaultModule();
					}
				}
			} else {
				return $this->loadDefaultModule();
			}
			
			return $this;
		}
		
		/**
		 * Default template choicer
		 * 
		 * @access  protected
		 * @return  self
		**/
		protected function templateHandler()
		{
			if ($action = $this->getCurrentAction()) {
				while (true) {
					$candidate = $this->templateDir . DIRECTORY_SEPARATOR .
							implode(DIRECTORY_SEPARATOR, $action);
					if (is_file($candidate . $this->templateExt)) {
						$this->template = implode(DIRECTORY_SEPARATOR, $action);
						break;
					} elseif (is_dir($candidate)) {
						return $this->loadDefaultTemplate(implode(DIRECTORY_SEPARATOR, $action));
					} elseif ($action) {
						array_pop($action);
					} else {
						return $this->loadDefaultTemplate();
					}
				}
			} else {
				return $this->loadDefaultTemplate();
			}
			
			return $this;
		}
		
		/**
		 * Loads default module file from passed directory
		 * 
		 * @param   string  path to directory with default module from module directory
		 * @access  protected
		 * @return  self
		**/
		protected function loadDefaultModule($path = '')
		{
			$module = trim(file_get_contents($this->moduleDir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $this->default));
			if (is_dir($this->moduleDir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $module)) {
				return $this->loadDefaultModule($path . DIRECTORY_SEPARATOR . $module);
			} else {
				require_once $this->moduleDir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $module . $this->moduleExt;
				$this->module = new $module;
			}
			
			return $this;
		}
		
		/**
		 * Loads default template file from passed directory
		 * 
		 * @param   string  path to directory with default module from module directory
		 * @access  protected
		 * @return  self
		**/
		protected function loadDefaultTemplate($path = '')
		{
			if (!is_file($this->templateDir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $this->default)
				&& '' != $path
				) {
				return $this->loadDefaultTemplate(substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR)));
			}
			
			$this->template = trim(file_get_contents($this->templateDir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $this->default));
			if ($path) {
				$this->template = $path . DIRECTORY_SEPARATOR . $this->template;
			}
			
			if (is_dir($this->templateDir . DIRECTORY_SEPARATOR . $this->template))
				return $this->loadDefaultTemplate($this->template);
			else
				return $this;
		}
		
		/**
		 * Returns current action taked from defined environment
		 * 
		 * @access  protected
		 * @return  array	   list of actions
		**/
		protected function getCurrentAction($override = false)
		{
			static $result;
			
			if ($result && !$override) {
				return $result;
			} else
				$result = array();
			
			$action = null;

			foreach ($this->globals as $global) {
				if (isset($GLOBALS[$global])) {
					$global = $GLOBALS[$global];
				} else {
					continue;
				}
				if (isset($global[$this->action])
					&& preg_match('/^\w+$/', $global[$this->action])
				) {
					$result[] = $action = $global[$this->action];
					while (true) {
						if (isset($global[$action])
							&& preg_match('/^\w+$/', $global[$this->action])
						) {
							$result[] = $action = $global[$action];
						} else {
							break 2;
						}
					}
				}
			}

			return $result;
		}
	}
?>