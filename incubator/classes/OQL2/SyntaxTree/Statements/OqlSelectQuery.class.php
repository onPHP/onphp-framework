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
	final class OqlSelectQuery extends OqlBindableNodeWrapper
	{
		private $distinct		= false;
		private $properties		= array();
		private $where			= array();
		private $whereLogic		= array();
		private $groupChain		= array();
		private $orderChain		= array();
		private $havingChain	= array();
		
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
		public function addProperties(OqlProjectionClause $clause)
		{
			$this->properties[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setProperties(OqlProjectionClause $clause)
		{
			$this->properties = array($clause);
			
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
		
		/**
		 * @return OqlSelectQuery
		**/
		public function where(OqlExpressionClause $clause, $logic = null)
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
		public function andWhere(OqlExpressionClause $clause)
		{
			$this->where($clause, BinaryExpression::EXPRESSION_AND);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function orWhere(OqlExpressionClause $clause)
		{
			$this->where($clause, BinaryExpression::EXPRESSION_OR);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setWhere(OqlExpressionClause $clause)
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
		public function addGroupBy(OqlProjectionClause $clause)
		{
			$this->groupChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setGroupBy(OqlProjectionClause $clause)
		{
			$this->groupChain = array($clause);
			
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
		public function addOrderBy(OqlOrderClause $clause)
		{
			$this->orderChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setOrderBy(OqlOrderClause $clause)
		{
			$this->orderChain = array($clause);
			
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
		public function addHaving(OqlProjectionClause $clause)
		{
			$this->havingChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setHaving(OqlProjectionClause $clause)
		{
			$this->havingChain = array($clause);
			
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
		 * @return Criteria
		**/
		public function toValue()
		{
			$criteria = parent::toValue()->
				setDistinct($this->distinct);
			
			$parameters = $this->pool->getList();
			
			$projections = array_merge(
				$this->properties,
				$this->groupChain,
				$this->havingChain
			);
			foreach ($projections as $clause) {
				$criteria->addProjection(
					$clause->
						bindAll($parameters)->
						toProjection()
				);
			}
			
			if ($this->where) {
				if (count($this->where) == 1) {
					$clause = reset($this->where);
					
					$criteria->add(
						$clause->
							bindAll($parameters)->
							toLogic()
					);
				
				} else {
					$logic = Expression::chain();
					foreach ($this->where as $key => $clause) {
						$expression = $clause->
							bindAll($parameters)->
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
						bindAll($parameters)->
						toOrder()
				);
			}
			
			return $criteria;
		}
		
		protected function checkNode(OqlSyntaxNode $node)
		{
			Assert::isTrue($node instanceof OqlCriteriaNode);
		}
	}
?>