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
	 * Just container of members and methods
	 * for next their using in output shablons
	**/
	class DummyTemplate
	{
		const FUNCTION_EXISTS		=
			'Function with same name already exists: use another one';
		
		const FUNCTION_NOT_EXISTS	= 'Function does not exists';
    	
		/**
		 * contains inner variables
		**/
		protected $variables = array();

		/**
		 * contains inner functions
		**/
		private $functions = array();

		/**
		 * contains value which Dummy returns when no inner variable or function
		 * named as called was set
		**/
		private $default = '';

		/// boolean flag shows: set somebody $default or it has default value
		private $defaultSet = false;

		/**
		 * sets value which Dummy returns when no variable or function
		 * named as called set
		 * 
		 * @param	$default	mixed	new default value
		 * @return	void
		**/
		public function setDefault($default)
		{
			$this->default		= $default;
			$this->defaultSet	= true;
			
			return $this;
		}

		/**
		 * Returns value for variable or function which
		 * is not set
		 * 
		 * @return	mixed	Dummy default value
		**/
		public function getDefault()
		{
			return $this->default;
		}

		/**
		 * Returns true if somebody set Dummy::$default
		 * 
		 * @return	boolean	true if somebody set Dummy::$default
		 *					false otherwise
		**/
		public function issetDefault()
		{
			return $this->defaultSet;
		}

		/**
		 * Creates Dummy::functionFame($args) using create_Fnction
		 * 
		 * @param	$functionName	name of function
		 * @param	$arguments		function args
		 * @param	$functionBody	code
		 * @return					unique name of lambda-style function in success
		 *							error otherwise
		**/
		public function createFunction($functionName, $arguments, $functionBody)
		{
			if (!array_key_exists ($functionName, $this->functions)) {
				$this->functions[$functionName] =
					create_function($arguments, $functionBody);
				
				return $this->functions[$functionName];
			} else {
				throw new Exception(self::FUNCTION_EXISTS);
			}
		}

		/**
		 * Returns value of Dummy::variable_name
		 * 
		 * @param	$variableName	string	variable name
		 * @return	mixed					value of variable_name
		 *									if variable_name was set
		 *									default value otherwise
		**/
		public function __get($variableName)
		{
			if (array_key_exists($variableName, $this->variables)) {
				return $this->variables[$variableName];
			} else {
				return $this->default;
			}
		}

		/**
		 * creates Dummy::variable_name if not exists and
		 * sets $value to Dummy::variable_name
		 * 
		 * @return	void
		**/
		public function __set($variableName, $value)
		{
			$this->variables[$variableName] = $value;
			
			return $this;
		}

		/**
		 * Calls StaticDummy::function_name($params)
		 * 
		 * @return	mixed	function result if function_name
		 *					was set, default value otherwise
		**/
		public function __call($functionName, $params /* , ... */)
		{
			if (array_key_exists($functionName, $this->functions)) {
				if (2 == func_num_args()) {
					return call_user_func($this->functions[$functionName], $params);
				} else {
					$args = func_get_args();
					array_pop($args);
					return call_user_func_array($this->functions[$functionName], $args);
				}
			} else {
				return $this->default;
			}
		}
	}
?>