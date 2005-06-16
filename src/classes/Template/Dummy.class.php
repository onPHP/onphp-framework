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

	/**
	 * Just container of members and methods
	 * for next their using in output shablons
	 *
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @access		public
	 * @version		1.0.0
	 * @since		PHP 5.0.0
	 * @package		Template_Dummy
	 * @category	Template
	 * @todo		RENAMEME
	**/
	class Dummy
	{
		const FUNCTION_EXISTS       = 'Function with same name already exists: use another one';
		const FUNCTION_NOT_EXISTS   = 'Function does not exists';
    
		/**
		 * @var		array		contains inner variables
		 * @access	protected
		**/
		protected $variables = array();

		/**
		 * @var		array		contains inner functions
		 * @access	private
		**/
		private $functions = array();

		/**
		 * @var		mixed	contains value which Dummy returns when no inner variable or function
		 *					named as called was set
		 * @access	private
		**/
		private $default = '';

		/**
		 * @var      boolean flag shows: set somebody $default or it has default value
		 * @access   private
		**/
		private $defaultSet = false;

		/**
		 * sets value which Dummy returns when no variable or function
		 * named as called set
		 * 
		 * @param	mixed	$default	new default value
		 * @return	void
		 * @access	public
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
		 * @access	public
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
		 * @access	public
		**/
		public function issetDefault()
		{
			return $this->defaultSet;
		}

		/**
		 * Creates Dummy::functionFame($args) using create_Fnction
		 * 
		 * @param	string	$functionName	name of function
		 * @param	string	$args			function args
		 * @param	string	$functionBody	code
		 * @return	string					unique name of lambda-style function in success
		 *									error otherwise
		 * @access	public
		**/
		public function createFunction($functionName, $arguments, $functionBody)
		{
			if (!array_key_exists ($functionName, $this->functions)) {
				$this->functions[$functionName] = create_function($arguments, $functionBody);
				return $this->functions[$functionName];
			} else {
				throw new Exception(self::FUNCTION_EXISTS);
			}
		}

		/**
		 * Returns value of Dummy::variable_name
		 * 
		 * @param	string	$variable_name	variable name
		 * @return	mixed					value of variable_name
		 *									if variable_name was set
		 *									default value otherwise
		 * @access	public
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
		 * @param	string	$variable_name	variable name
		 * @param	mixed	$value			variable value
		 * @return	void
		 * @access	public
		**/
		public function __set($variableName, $value)
		{
			$this->variables[$variableName] = $value;
			
			return $this;
		}

		/**
		 * Calls StaticDummy::function_name($params)
		 * 
		 * @param	string	$function_name	function name
		 * @param	mixed	$params			function parametres
		 * @return	mixed					function result if function_name
		 *									was set, default value otherwise
		 * @access	public
		**/
		public function __call($functionName, $params)
		{
			if (array_key_exists($functionName, $this->functions)) {
				if (2 == func_num_args()) {
					return call_user_func_array($this->functions[$functionName], $params);
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
