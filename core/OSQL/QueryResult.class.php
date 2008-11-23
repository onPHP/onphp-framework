<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Holder for query's execution information.
	 * 
	 * @ingroup OSQL
	**/
	class QueryResult implements Identifiable
	{
		private $list		= array();
		private $count		= null;
		private $affected	= null;
		private $query		= null;
		
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
		
		public function getQuery()
		{
			return $this->query;
		}
		
		public function setQuery(SelectQuery $query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		public function setList($list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function getCount()
		{
			return $this->count;
		}
		
		public function setCount($count)
		{
			$this->count = $count;
			
			return $this;
		}
		
		public function getAffected()
		{
			return $this->affected;
		}
		
		public function setAffected($affected)
		{
			$this->affected = $affected;
			
			return $this;
		}
	}
?>