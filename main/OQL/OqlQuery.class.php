<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
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
	abstract class OqlQuery implements Stringable
	{
		protected $dao			= null;
		protected $parameters	= array();
		
		private $query	= null;
		
		public function getQuery()
		{
			return $this->query;
		}
		
		/**
		 * @return OqlQuery
		**/
		public function setQuery($query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		/**
		 * @return ProtoDAO
		**/
		public function getDao()
		{
			return $this->dao;
		}
		
		/**
		 * @return OqlQuery
		**/
		public function setDao(ProtoDAO $dao)
		{
			$this->dao = $dao;
			
			return $this;
		}
		
		/**
		 * @return OqlQuery
		**/
		public function bind($index, $value)
		{
			$this->parameters[$index] = $value;
			
			return $this;
		}
		
		/**
		 * @return OqlQuery
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