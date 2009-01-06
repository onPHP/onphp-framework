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
	final class OqlSelectGroupByClause extends OqlQueryClause
	{
		protected $list = array();
		
		/**
		 * @return OqlSelectGroupByClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSelectGroupByClause
		**/
		public function add(OqlQueryExpression $property)
		{
			$this->list[] = $property;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return OqlSelectGroupByClause
		**/
		public function setList(array $list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectGroupByClause
		**/
		public function dropList()
		{
			$this->list = array();
			
			return $this;
		}
		
		/**
		 * @return ProjectionChain
		**/
		public function toProjection()
		{
			$projection = Projection::chain();
			foreach ($this->list as $property) {
				$projection->add(
					$property->evaluate($this->parameters)
				);
			}
			
			return $projection;
		}
	}
?>