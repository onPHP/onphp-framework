<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	namespace Onphp;

	abstract class OqlQueryClause
	{
		protected $parameters = array();
		
		/**
		 * @return \Onphp\OqlQueryClause
		**/
		public function bind($index, $value)
		{
			$this->parameters[$index] = $value;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\OqlQueryClause
		**/
		public function bindNext($value)
		{
			end($this->parameters);
			
			return $this->bind(key($this->parameters) + 1, $value);
		}
		
		/**
		 * @return \Onphp\OqlQueryClause
		**/
		public function bindAll(array $parameters)
		{
			if ($parameters)
				$this->parameters = $parameters;
			
			return $this;
		}
	}
?>