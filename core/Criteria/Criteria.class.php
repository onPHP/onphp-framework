<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov, Anton E. Lebedevich     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Criteria
	**/
	final class Criteria implements Stringable
	{
		private $dao	= null;
		private $logic	= null;
		private $order	= null;
		
		private $limit	= null;
		private $offset	= null;
		
		/**
		 * @return Criteria
		**/
		public static function create(/* StorableDAO */ $dao = null)
		{
			return new self($dao);
		}
		
		public function __construct(/* StorableDAO */ $dao = null)
		{
			if ($dao)
				Assert::isTrue($dao instanceof StorableDAO);
			
			$this->dao = $dao;
			$this->logic = Expression::andBlock();
		}
		
		/**
		 * @return Criteria
		**/
		public function setDao(StorableDAO $dao)
		{
			$this->dao = $dao;
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function add(LogicalObject $logic)
		{
			Assert::isFalse(
				$logic instanceof OrderBy,
				
				'common typo'
			);
			
			$this->logic->expAnd($logic);
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function addOrder(LogicalObject $order)
		{
			$this->order[] = $order;
			
			return $this;
		}
		
		public function getLimit()
		{
			return $this->limit;
		}
		
		/**
		 * @return Criteria
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
		 * @return Criteria
		**/
		public function setOffset($offset)
		{
			$this->offset = $offset;
			
			return $this;
		}
		
		public function getList()
		{
			try {
				return $this->dao->getListByQuery($this->toSelectQuery());
			} catch (ObjectNotFoundException $e) {
				return array();
			}
		}
		
		public function toString()
		{
			return
				$this->toSelectQuery()->
				toDialectString(
					DBPool::getByDao($this->dao)->getDialect()
				);
		}
		
		/**
		 * @return SelectQuery
		**/
		public function toSelectQuery()
		{
			$query =
				$this->dao->makeSelectHead()->
				limit($this->limit, $this->offset);
			
			if ($this->logic->getSize()) {
				$query->
					andWhere(
						$this->logic->toMapped($this->dao, $query)
					);
			}
			
			if ($this->order) {
				for ($size = count($this->order), $i = 0; $i < $size; ++$i) {
					$query->
						orderBy(
							$this->order[$i]->toMapped($this->dao, $query)
						);
				}
			}
			
			return $query;
		}
	}
?>