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
	final class OqlSelectQuery extends OqlQuery
	{
		private $distinct		= false;
		private $properties		= array();
		private $where			= array();
		private $whereLogic		= array();
		private $groupChain		= array();
		private $orderChain		= array();
		private $havingChain	= array();
		private $limit			= null;
		private $offset			= null;
		
		/**
		 * @return OqlSelectQuery
		**/
		public static function create()
		{
			return new self;
		}
		
		public function isDistinct()
		{
			return $this->distinct;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setDistinct($orly = true)
		{
			$this->distinct = ($orly === true);
			
			return $this;
		}
		
		public function getProperties()
		{
			return $this->properties;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addProperties(OqlSelectPropertiesClause $clause)
		{
			$this->properties[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setProperties(OqlSelectPropertiesClause $clause)
		{
			$this->properties = array();
			$this->properties[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropProperties()
		{
			$this->properties = array();
			
			return $this;
		}
		
		public function getWhere()
		{
			return $this->where;
		}
		
		public function getWhereLogic()
		{
			return $this->whereLogic;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function where(OqlWhereClause $clause, $logic = null)
		{
			if ($this->where && !$logic) {
				throw new WrongArgumentException(
					'you have to specify expression logic'
				);
			
			} else {
				if (!$this->where && $logic)
					$logic = null;
				
				$this->where[] = $clause;
				$this->whereLogic[] = $logic;
			}
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function andWhere(OqlWhereClause $clause)
		{
			$this->where($clause, BinaryExpression::EXPRESSION_AND);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function orWhere(OqlWhereClause $clause)
		{
			$this->where($clause, BinaryExpression::EXPRESSION_OR);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setWhere(OqlWhereClause $clause)
		{
			$this->where = array();
			$this->whereLogic = array();
			$this->where($clause);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropWhere()
		{
			$this->where = array();
			$this->whereLogic = array();
			
			return $this;
		}
		
		public function getGroupBy()
		{
			return $this->groupChain;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addGroupBy(OqlSelectProjectionClause $clause)
		{
			$this->groupChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setGroupBy(OqlSelectProjectionClause $clause)
		{
			$this->groupChain = array();
			$this->groupChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropGroupBy()
		{
			$this->groupChain = array();
			
			return $this;
		}
		
		public function getOrderBy()
		{
			return $this->orderChain;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addOrderBy(OqlSelectOrderByClause $clause)
		{
			$this->orderChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setOrderBy(OqlSelectOrderByClause $clause)
		{
			$this->orderChain = array();
			$this->orderChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropOrderBy()
		{
			$this->orderChain = array();
			
			return $this;
		}
		
		public function getHaving()
		{
			return $this->havingChain;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addHaving(OqlSelectProjectionClause $clause)
		{
			$this->havingChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setHaving(OqlSelectProjectionClause $clause)
		{
			$this->havingChain = array();
			$this->havingChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropHaving()
		{
			$this->havingChain = array();
			
			return $this;
		}
		/**
		 * @return OqlQueryParameter
		**/
		public function getLimit()
		{
			return $this->limit;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setLimit(OqlQueryParameter $limit)
		{
			$this->limit = $limit;
			
			return $this;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		public function getOffset()
		{
			return $this->offset;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setOffset(OqlQueryParameter $offset)
		{
			$this->offset = $offset;
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function toCriteria()
		{
			$criteria = Criteria::create($this->dao)->
				setDistinct($this->distinct);
			
			$projections = array_merge(
				$this->properties,
				$this->groupChain,
				$this->havingChain
			);
			foreach ($projections as $clause) {
				$criteria->addProjection(
					$clause->
						bindAll($this->parameters)->
						toProjection()
				);
			}
			
			if ($this->where) {
				if (count($this->where) == 1) {
					$clause = reset($this->where);
					
					$criteria->add(
						$clause->
							bindAll($this->parameters)->
							toLogic()
					);
				
				} else {
					$logic = Expression::chain();
					foreach ($this->where as $key => $clause) {
						$expression = $clause->
							bindAll($this->parameters)->
							toLogic();
						
						if (
							$this->whereLogic[$key]
							== BinaryExpression::EXPRESSION_AND
						) {
							$logic->expAnd($expression);
						} else {
							$logic->expOr($expression);
						}
					}
					
					$criteria->add($logic);
				}
			}
			
			foreach ($this->orderChain as $clause) {
				$criteria->addOrder(
					$clause->
						bindAll($this->parameters)->
						toOrder()
				);
			}
			
			if ($this->limit)
				$criteria->setLimit(
					$this->limit->evaluate($this->parameters)
				);
			
			if ($this->offset)
				$criteria->setOffset(
					$this->offset->evaluate($this->parameters)
				);
			
			return $criteria;
		}
	}
?>