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
	final class Criteria
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
			$this->logic->expAnd($logic);
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function addOrder(OrderBy $order)
		{
			$this->order[] = $order;
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function limit($limit = null, $offset = null)
		{
			$this->limit = $limit;
			$this->offset = $offset;
			
			return $this;
		}
		
		public function getList()
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
			
			try {
				return $this->dao->getListByQuery($query);
			} catch (ObjectNotFoundException $e) {
				return array();
			}
		}
	}
?>