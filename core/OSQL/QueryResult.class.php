<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Holder for query's execution information.
	 * 
	 * @ingroup OSQL
	**/
	final class QueryResult implements Identifiable
	{
		private $list		= array();
		
		private $count		= 0;
		private $affected	= 0;
		
		private $query		= null;
		
		/**
		 * @return QueryResult
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getId()
		{
			return '_result_'.$this->query->getId();
		}
		
		public function setId($id)
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * @return SelectQuery
		**/
		public function getQuery()
		{
			return $this->query;
		}
		
		/**
		 * @return QueryResult
		**/
		public function setQuery(SelectQuery $query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return QueryResult
		**/
		public function setList($list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function getCount()
		{
			return $this->count;
		}
		
		/**
		 * @return QueryResult
		**/
		public function setCount($count)
		{
			$this->count = $count;
			
			return $this;
		}
		
		public function getAffected()
		{
			return $this->affected;
		}
		
		/**
		 * @return QueryResult
		**/
		public function setAffected($affected)
		{
			$this->affected = $affected;
			
			return $this;
		}
	}
