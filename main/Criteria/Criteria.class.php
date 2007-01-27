<?php
/****************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @see http://www.hibernate.org/hib_docs/v3/reference/en/html/querycriteria.html
	 * 
	 * @ingroup Criteria
	**/
	final class Criteria implements Stringable, DialectString
	{
		private $dao		= null;
		private $logic		= null;
		private $strategy	= null;
		private $projection	= null;
		
		private $distinct	= false;
		
		private $order	= array();
		
		private $limit	= null;
		private $offset	= null;
		
		private $collections = array();
		
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
			
			if ($dao instanceof ComplexBuilderDAO)
				$this->strategy = FetchStrategy::join();
			else
				$this->strategy = FetchStrategy::cascade();
		}
		
		/**
		 * @return StorableDAO
		**/
		public function getDao()
		{
			return $this->dao;
		}
		
		/**
		 * @return Criteria
		**/
		public function setDao(StorableDAO $dao)
		{
			if ($this->strategy->getId() == FetchStrategy::JOIN)
				Assert::isTrue(
					$dao instanceof ComplexBuilderDAO,
					'your DAO does not support join fetch strategy'
				);
			
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
		public function addOrder(/* MapableObject */ $order)
		{
			if (!$order instanceof MappableObject)
				$order = new OrderBy($order);
			
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
		
		/**
		 * @return FetchStrategy
		**/
		public function getFetchStrategy()
		{
			return $this->strategy;
		}
		
		/**
		 * @return Criteria
		**/
		public function setFetchStrategy(FetchStrategy $strategy)
		{
			if (
				$this->dao
				&& ($strategy->getId() == FetchStrategy::JOIN)
			) {
				Assert::isTrue(
					$this->dao instanceof ComplexBuilderDAO,
					'your DAO does not support join fetch strategy'
				);
			}
			
			$this->strategy = $strategy;
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function setProjection(ObjectProjection $chain)
		{
			$this->projection = $chain;
			
			return $this;
		}
		
		/**
		 * @return ProjectionChain
		**/
		public function getProjection()
		{
			return $this->projection;
		}
		
		/**
		 * @return Criteria
		**/
		public function setDistinct($orly = true)
		{
			$this->distinct = ($orly === true);
			
			return $this;
		}
		
		public function isDistinct()
		{
			return $this->distinct;
		}
		
		/**
		 * @return Criteria
		**/
		public function fetchCollection($path, $lazy = false)
		{
			Assert::isBoolean($lazy);
			
			$this->collections[$path] = $lazy;
			
			return $this;
		}
		
		public function get()
		{
			try {
				$list = array($this->dao->getByQuery($this->toSelectQuery()));
			} catch (ObjectNotFoundException $e) {
				return null;
			}
			
			if (!$this->collections)
				return reset($list);
			
			return reset($this->dao->fetchCollections($this->collections, $list));
		}
		
		public function getList()
		{
			try {
				$list = $this->dao->getListByQuery($this->toSelectQuery());
			} catch (ObjectNotFoundException $e) {
				return array();
			}
			
			if (!$this->collections)
				return $list;
			
			return $this->dao->fetchCollections($this->collections, $list);
		}
		
		public function getCustomList()
		{
			try {
				$this->dao->getCustomList($this->toSelectQuery());
			} catch (ObjectNotFoundException $e) {
				return array();
			}
		}
		
		public function getPropertyList()
		{
			try {
				$this->dao->getCustomRowList($this->toSelectQuery());
			} catch (ObjectNotFoundException $e) {
				return array();
			}
		}
		
		public function toString()
		{
			return $this->toDialectString(
				DBPool::getByDao($this->dao)->getDialect()
			);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $this->toSelectQuery()->toDialectString($dialect);
		}
		
		/**
		 * @return SelectQuery
		**/
		public function toSelectQuery()
		{
			if ($this->projection) {
				$query =
					$this->getProjection()->process(
						$this,
						OSQL::select()->from($this->dao->getTable())
					);
			} else
				$query = $this->dao->makeSelectHead();
			
			$query->
				limit($this->limit, $this->offset)->
				setFetchStrategyId($this->strategy->getId());
			
			if ($this->distinct)
				$query->distinct();
			
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
			
			if (
				!$this->projection
				&& $this->strategy->getId() == FetchStrategy::JOIN
			) {
				$proto = call_user_func(array($this->dao->getObjectName(), 'proto'));
				
				foreach ($proto->getPropertyList() as $property) {
					if (
						$property->getRelationId() == MetaRelation::ONE_TO_ONE
						&& !$property->isGenericType()
					) {
						$dao = call_user_func(
							array($property->getClassName(), 'dao')
						);
						
						if ($dao->getTable() == $this->dao->getTable()) {
							$alias = $property->getDumbName();
							
							$fields = $dao->getFields();
							
							$fields[
								array_search($this->dao->getIdName(), $fields)
							] = new SelectField(
								new DBField($property->getDumbIdName(), $dao->getTable()),
								
								$this->dao->getJoinPrefix($property->getDumbName())
								.$dao->getIdName()
							);
						} else {
							$alias = null;
							$fields = $dao->getFields();
						}
						
						if (!$query->hasJoinedTable($dao->getTable())) {
							$logic =
								Expression::eq(
									DBField::create(
										$property->getDumbIdName(),
										$this->dao->getTable()
									),
									
									DBField::create(
										$dao->getIdName(),
										$alias
											? $alias
											: $dao->getTable()
									)
								);
							
							if ($property->isRequired())
								$query->join($dao->getTable(), $logic, $alias);
							else
								$query->leftJoin($dao->getTable(), $logic, $alias);
						}
						
						$query->arrayGet(
							$fields,
							$dao->getJoinPrefix($property->getDumbName())
						);
					}
				}
			}
			
			return $query;
		}
		
		/**
		 * @return AbstractProtoClass
		**/
		private function getProto()
		{
			return
				call_user_func(
					array($this->dao->getObjectName(), 'proto')
				);
		}
	}
?>