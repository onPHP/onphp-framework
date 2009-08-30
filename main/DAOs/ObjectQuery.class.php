<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Various information holder for communication
	 * between StorableDAO implementations and controllers.
	 * 
	 * @deprecated by Criteria
	 * 
	 * @see StorableDAO
	 * @see Controller
	 * 
	 * @ingroup DAOs
	**/
	final class ObjectQuery
	{
		const SORT_ASC		= 0x0001;
		const SORT_DESC		= 0x0002;
		const SORT_IS_NULL	= 0x0003;
		const SORT_NOT_NULL = 0x0004;
		
		private $sort		= array();
		private $logic		= array();

		private $current	= null;

		private $limit		= null;
		private $offset		= null;
		
		/**
		 * @return ObjectQuery
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function sort($name)
		{
			if ($this->current)
				$this->sort[$this->current] = self::SORT_ASC;

			$this->current = $name;		

			return $this;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function dropSort()
		{
			$this->current = null;
			$this->sort = array();
			
			return $this;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function asc()
		{
			return $this->direction(self::SORT_ASC);
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function desc()
		{
			return $this->direction(self::SORT_DESC);
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function isNull()
		{
			return $this->direction(self::SORT_IS_NULL);
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function notNull()
		{
			return $this->direction(self::SORT_NOT_NULL);
		}
		
		public function getLimit()
		{
			return $this->limit;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function setLimit($limit)
		{
			$this->limit = $limit;
			
			return $this;
		}
		
		public function getOffset()
		{
			return $this->offset;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function setOffset($offset)
		{
			$this->offset = $offset;
			
			return $this;
		}
		
		public function getLogic()
		{
			return $this->logic;
		}
		
		/**
		 * @return ObjectQuery
		**/
		public function addLogic(LogicalObject $exp)
		{
			$this->logic[] = $exp;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function toSelectQuery(StorableDAO $dao)
		{
			// cleanup
			if ($this->current) {
				$this->sort[$this->current] = self::SORT_ASC;
				$this->current = null;
			}
			
			$query = $dao->makeSelectHead();
			
			foreach ($this->logic as $exp) {
				$query->andWhere(
					$exp->toMapped($dao, $query)
				);
			}
			
			foreach ($this->sort as $property => $direction) {
				switch ($direction) {
					case self::SORT_ASC:
						
						$query->orderBy(
							OrderBy::create($property)->
							asc()->
							toMapped($dao, $query)
						);
						
						break;
					
					case self::SORT_DESC:
						
						$query->orderBy(
							OrderBy::create($property)->
							desc()->
							toMapped($dao, $query)
						);
						
						break;
					
					case self::SORT_IS_NULL:
						
						$query->orderBy(
							Expression::isNull($property)->toMapped($dao, $query)
						);
						
						break;
					
					case self::SORT_NOT_NULL:
						
						$query->orderBy(
							Expression::notNull($property)->toMapped($dao, $query)
						);
						
						break;
					
					default:
						
						throw new WrongStateException(
							'unknown or unsupported '.
							"direction '{$direction}'"
						);
				}
			}
			
			return $query->limit($this->limit, $this->offset);
		}
		
		/**
		 * @return ObjectQuery
		**/
		private function direction($constant)
		{
			if (!$this->current)
				throw new WrongStateException(
					'specify property name first'
				);
			
			$this->sort[$this->current] = $constant;
			
			$this->current = null;
			
			return $this;
		}
	}
?>