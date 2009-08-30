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
	 * Features are not in Dummy:
	 * 1. Keeps output info about templates
	 * 2. Outputs templates
	 * 
	 * Attention: it is serve's class for PropertyFilter only
	**/
	class DummyTemplateWrapper extends DummyTemplate
	{
		private $parent = null;
		
		/**
		 * Returns value of PropertyFilter::$variableName
		 * hanled by default handler
		 * 
		 * @return	mixed	value of variable_name
		 *					if variable_name was set
		 *					default value otherwise
		**/
		public function __get($var)
		{
			$params = $this->parent->getDefaultParams();

			if (isset($this->variables[$var])) {
				if (is_scalar($this->variables[$var])) {
					array_splice($params, 0, 0, array($this->variables[$var]));
					return call_user_func_array(array($this->parent, $this->parent->getDefaultHandler()), $params);
				} elseif (is_array($this->variables[$var])) {
					// array_walk_recursive through array variable
					$result = $this->variables[$var];
					array_walk_recursive($result, array($this->parent, $this->parent->getDefaultAWHandler()), $params);
					return $result;
				} else {
					// array_walk_recursive through array variable
					return $this->variables[$var];
				}
			} else {
				array_splice($params, 0, 0, array($this->getDefault()));
				return call_user_func_array(array($this->parent, $this->parent->getDefaultHandler()), $params);
			}
		}
		
		public function __construct($propertyFilter)
		{
			$this->parent = $propertyFilter;
		}
		
	}
?>