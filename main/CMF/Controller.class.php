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
	 * Abstract controller
	**/
	class Controller
	{
		const NOT_SUPPORTED_GLOBAL = 'Not supported global array';
		
		/**
		 * CommonModule	current active module
		**/
		protected $module;
		
		/**
		 * current template file basename
		**/
		protected $template;
		
		/**
		 * template engine instance
		**/
		protected $templateEngine;
		
		/**
		 * environment variable for action selecting
		**/
		private $globals = array('_GET', '_POST', '_SESSION', '_COOKIE');
		
		/**
		 * name of action key
		**/
		private $action	 = 'action';
		
		/**
		 * handler of module loading callable by call_user_func
		**/
		private $moduleHandler = null;
		
		/**
		 * handler for template choice callable by call_user_func
		**/
		private $templateHandler = null;
		
		/**
		 * full path to directory with modules
		**/
		protected $moduleDir = null;
		
		/**
		 * full path to directory with templates
		**/
		protected $templateDir = null;
		
		/**
		 * extension of module
		**/
		protected $moduleExt = EXT_MOD;
		
		/**
		 * extension of module
		**/
		protected $templateExt = EXT_TPL;
		
		/**
		 * name of file contains default module name
		**/
		protected $default = 'default';
		
		/**
		 * array of parametres which should be assigned to template
		**/
		protected $parameters		= array();
	
		public static function create()
		{
			return new Controller;
		}
		
		/**
		 * Sets flag of using global
		 * 
		 * @param	$global		string	global array name
		 * @param   $position	integer	position in array of globals (starts from 0)
		 * 
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
		 * @param	$global	string	global array name
		 * 
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
		 * @param	$handler	array or string callable of call_user_func
		 * 
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
		 * @return  mixed array or string callable of call_user_func
		**/
		public function getModuleHandler()
		{
			return $this->moduleHandler;
		}
		
		/**
		 * Sets function name of template handler, default bundled
		 * 
		 * @param   $handler	mixed	array or string callable of call_user_func
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
		 * @return  mixed array or string callable of call_user_func
		**/
		public function getTemplateHandler()
		{
			return $this->templateHandler;
		}
		
		/**
		 * Sets path modules directory
		 * 
		 * @param   $dir	string	full path to modules directory
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
		 * @return string
		**/
		public function getModuleDir()
		{
			return $this->moduleDir;
		}
		
		/**
		 * Sets path templates directory
		 * 
		 * @param	$dir	string	full path to templates directory
		 * @return	self
		**/
		public function setTemplateDir($dir)
		{
			$this->templateDir = $dir;
			
			return $this;
		}
		
		/**
		 * Returns full path to templates directtory
		 * 
		 * @return	string
		**/
		public function getTemplateDir()
		{
			return $this->templateDir;
		}
		
		/**
		 * Sets modules extension
		 * 
		 * @param	$ext	string	new modules extension
		 * @return	self
		**/
		public function setModuleExt($ext)
		{
			$this->moduleExt = $ext;
			
			return $this;
		}
		
		/**
		 * Returns modules extension
		 * 
		 * @return string
		**/
		public function getModuleExt()
		{
			return $this->moduleExt;
		}
		
		/**
		 * Sets templates extension
		 * 
		 * @param	$ext	string	  new templates extension
		 * 
		 * @return	self
		**/
		public function setTemplateExt($ext)
		{
			$this->templateExt = $ext;
			
			return $this;
		}
		
		/**
		 * Returns full path to templates extension
		 * 
		 * @return string
		**/
		public function getTemplateExt()
		{
			return $this->templateExt;
		}
		
		/**
		 * Sets template engine instance
		 * 
		 * @param	$templateEngine	new template engine instance
		 * 
		 * @return	self
		**/
		public function setTemplateEngine($templateEngine)
		{
			$this->templateEngine = $templateEngine;
			
			return $this;
		}
		
		/**
		 * Returns template engine instance
		 * 
		 * @return mixed
		**/
		public function getTemplateEngine()
		{
			return $this->templateEngine;
		}
		
		/**
		 * Sets action env flag
		 * 
		 * @param	$action	string	new action env flag
		 * 
		 * @return	self
		**/
		public function setAction($action)
		{
			$this->action = $action;
			
			return $this;
		}
		
		/**
		 * Returns action env flag
		 * 
		 * @return string
		**/
		public function getAction()
		{
			return $this->action;
		}
		
		/**
		 * Sets file with default module name
		 * 
		 * @param	$default	string	new file with default module name
		 * 
		 * @return	self
		**/
		public function setDefault($default)
		{
			$this->default = $default;
			
			return $this;
		}
		
		/**
		 * Returns file with default module name
		 * 
		 * @return string
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
		 * @return CommonModule
		**/
		public function getModule()
		{
			return $this->module;
		}
		
		/**
		 * Returns current template name
		 * 
		 * @return string
		**/
		public function getTemplate()
		{
			return $this->template;
		}
		
		/**
		 * Main module handler
		 * 
		 * @return self
		**/
		public function doModel()
		{
			$this->moduleHandler();
			
			return
				$this->setParameters(
					$this->module->
						init()->
						process()->
						getParameters()
				);
		}
		
		/**
		 * Main view handler
		 * 
		 * @return self
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
		
		public function __construct()
		{
			$this->setModuleHandler(array($this, 'moduleHandler'));
			$this->setTemplateHandler(array($this, 'templateHandler'));
		}
		
		/**
		 * Default module loader
		 * 
		 * @return self
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
						return $this->loadDefaultModule(
							implode(DIRECTORY_SEPARATOR, $action)
							);
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
		 * @return self
		**/
		protected function templateHandler()
		{
			if ($action = $this->getCurrentAction()) {
				while (true) {
					$candidate =
						$this->templateDir . DIRECTORY_SEPARATOR
						.implode(DIRECTORY_SEPARATOR, $action);
					
					if (is_file($candidate . $this->templateExt)) {
						$this->template = implode(DIRECTORY_SEPARATOR, $action);
						break;
					} elseif (is_dir($candidate)) {
						return
							$this->loadDefaultTemplate(
								implode(DIRECTORY_SEPARATOR, $action)
							);
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
		 * @param	$path	string	path to directory with default module from module directory
		 * 
		 * @return	self
		**/
		protected function loadDefaultModule($path = '')
		{
			$module =
				trim(
					file_get_contents(
						$this->moduleDir . DIRECTORY_SEPARATOR
						. $path . DIRECTORY_SEPARATOR . $this->default
					)
				);
			
			if (
				is_dir(
					$this->moduleDir . DIRECTORY_SEPARATOR
					. $path . DIRECTORY_SEPARATOR . $module
				)
			) {
				return $this->loadDefaultModule($path . DIRECTORY_SEPARATOR . $module);
			} else {
				require_once
					$this->moduleDir . DIRECTORY_SEPARATOR
					. $path . DIRECTORY_SEPARATOR
					. $module . $this->moduleExt;
				
				$this->module = new $module;
			}
			
			return $this;
		}
		
		/**
		 * Loads default template file from passed directory
		 * 
		 * @param	$path	string	path to directory with default module from module directory
		 * 
		 * @return	self
		**/
		protected function loadDefaultTemplate($path = '')
		{
			if (
				!is_file(
					$this->templateDir . DIRECTORY_SEPARATOR
					. $path . DIRECTORY_SEPARATOR . $this->default
				)
				&& '' !== $path
			) {
				return
					$this->loadDefaultTemplate(
						substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR))
					);
			}
			
			$this->template =
				trim(
					file_get_contents(
						$this->templateDir . DIRECTORY_SEPARATOR
						. $path . DIRECTORY_SEPARATOR . $this->default
					)
				);
			
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
		 * @return	array	list of actions
		**/
		protected function getCurrentAction($override = false)
		{
			static $result;
			
			if ($result && !$override)
				return $result;
			else
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