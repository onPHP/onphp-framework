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
		private $properties		= array();
		private $groupChain		= array();
		private $havingChain	= array();
		
		private $whereExpression	= null;
		private $distinct			= false;
		private $limit				= null;
		private $offset				= null;
		private $orderChain			= array();
		
		/**
		 * @return OqlSelectQuery
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getProperties()
		{
			return $this->properties;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addProperties(OqlSelectProjectionClause $clause)
		{
			$this->properties[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setProperties(OqlSelectProjectionClause $clause)
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
		 * @return OqlQueryExpression
		**/
		public function getWhereExpression()
		{
			return $this->whereExpression;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setWhereExpression(OqlQueryExpression $whereExpression)
		{
			$this->whereExpression = $whereExpression;
			
			return $this;
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
		
		public function getOrder()
		{
			return $this->orderChain;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addOrder(OqlSelectOrderByClause $clause)
		{
			$this->orderChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function setOrder(OqlSelectOrderByClause $clause)
		{
			$this->orderChain = array();
			$this->orderChain[] = $clause;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function dropOrder()
		{
			$this->orderChain = array();
			
			return $this;
		}
		
		/**
		 * @return Criteria
		**/
		public function toCriteria()
		{
			$criteria = Criteria::create($this->dao)->
				setDistinct($this->distinct);
			
			if ($this->limit)
				$criteria->setLimit(
					$this->limit->evaluate($this->parameters)
				);
			
			if ($this->offset)
				$criteria->setOffset(
					$this->offset->evaluate($this->parameters)
				);
			
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
			
			if ($this->whereExpression)
				$criteria->add(
					$this->whereExpression->evaluate($this->parameters)
				);
			
			foreach ($this->orderChain as $clause) {
				$criteria->addOrder(
					$clause->
						bindAll($this->parameters)->
						toOrder()
				);
			}
			
			return $criteria;
		}
	}
?>