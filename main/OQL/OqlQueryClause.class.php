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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	abstract class OqlQueryClause implements Stringable
	{
		protected $parameters = array();
		
		private $query = null;
		
		public function getQuery()
		{
			return $this->query;
		}
		
		/**
		 * @return OqlQueryClause
		**/
		public function setQuery($query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		/**
		 * @return OqlQueryClause
		**/
		public function bind($index, $value)
		{
			$this->parameters[$index] = $value;
			
			return $this;
		}
		
		/**
		 * @return OqlQueryClause
		**/
		public function bindNext($value)
		{
			end($this->parameters);
			
			return $this->bind(key($this->parameters) + 1, $value);
		}
		
		public function toString()
		{
			return $this->query;
		}
	}
?>