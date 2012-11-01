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

	abstract class OqlQueryListedClause extends OqlQueryClause
	{
		protected $list = array();
		
		/**
		 * @return \Onphp\OqlQueryListedClause
		**/
		public function add(OqlQueryParameter $property)
		{
			$this->list[] = $property;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return \Onphp\OqlQueryListedClause
		**/
		public function setList(array $list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\OqlQueryListedClause
		**/
		public function dropList()
		{
			$this->list = array();
			
			return $this;
		}
	}
?>