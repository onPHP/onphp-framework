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
/* $Id: $ */

	/**
	 * @ingroup OQL
	**/
	final class OqlSelectQuery extends OqlQuery
	{
		private $projections		= array();
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
		
		public function getProjections()
		{
			return $this->projections;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addProjection(OqlQueryExpression $projection)
		{
			$this->projections[] = $projection;
			
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
		
		public function getOrderChain()
		{
			return $this->orderChain;
		}
		
		/**
		 * @return OqlSelectQuery
		**/
		public function addOrder(OqlQueryExpression $order)
		{
			$this->orderChain[] = $order;
			
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
			
			if ($this->projections) {
				if (count($this->projections) == 1) {
					$oqlProjection = reset($this->projections);
					$projection = $oqlProjection->evaluate($this->parameters);
					
				} else {
					$projection = Projection::chain();
					foreach ($this->projections as $oqlProjection)
						$projection->add(
							$oqlProjection->evaluate($this->parameters)
						);
				}
				
				$criteria->setProjection($projection);
			}
			
			if ($this->whereExpression)
				$criteria->add(
					$this->whereExpression->evaluate($this->parameters)
				);
				
			if ($this->orderChain) {
				if (count($this->orderChain) == 1) {
					$oqlOrder = reset($this->orderChain);
					$order = $oqlOrder->evaluate($this->parameters);
					
				} else {
					$order = OrderChain::create();
					foreach ($this->orderChain as $oqlOrder)
						$order->add(
							$oqlOrder->evaluate($this->parameters)
						);
				}
				
				$criteria->addOrder($order);
			}
			
			return $criteria;
		}
	}
?>