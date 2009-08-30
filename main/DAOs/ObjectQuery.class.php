<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Various information holder for communication
	 * between StorableDAO implementations and controllers.
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
		
		public static function create()
		{
			return new self;
		}

		public function sort($name)
		{
			if ($this->current)
				$this->sort[$this->current] = self::SORT_ASC;

			$this->current = $name;		

			return $this;
		}
		
		public function dropSort()
		{
			$this->current = null;
			$this->sort = array();
			
			return $this;
		}
		
		public function asc()
		{
			return $this->direction(self::SORT_ASC);
		}
		
		public function desc()
		{
			return $this->direction(self::SORT_DESC);
		}
		
		public function isNull()
		{
			return $this->direction(self::SORT_IS_NULL);
		}
		
		public function notNull()
		{
			return $this->direction(self::SORT_NOT_NULL);
		}
		
		public function getLimit()
		{
			return $this->limit;
		}
		
		public function setLimit($limit)
		{
			$this->limit = $limit;
			
			return $this;
		}
		
		public function getOffset()
		{
			return $this->offset;
		}
		
		public function setOffset($offset)
		{
			$this->offset = $offset;
			
			return $this;
		}
		
		public function getLogic()
		{
			return $this->logic;
		}
		
		public function addLogic(LogicalExpression $exp)
		{
			$this->logic[] = $exp;
			
			return $this;
		}
		
		public function toSelectQuery(StorableDAO $dao)
		{
			// cleanup
			if ($this->current) {
				$this->sort[$this->current] = self::SORT_ASC;
				$this->current = null;
			}
			
			$map = $dao->getMapping();

			$query = $dao->makeSelectHead();
			
			foreach ($this->sort as $property => $direction) {
				if (array_key_exists($property, $map)) {
					
					if ($map[$property] === null)
						$field = $property;
					else
						$field = $map[$property];
					
					if (is_array($field)) {
						switch ($direction) {
							case self::SORT_ASC:

								foreach ($field as $col)
									$query->orderBy($col)->asc();

								break;

							case self::SORT_DESC:

								foreach ($field as $col)
									$query->orderBy($col)->desc();

								break;

							case self::SORT_IS_NULL:
								
								$chain = new LogicalChain();
								
								foreach ($field as $col)
									$chain->expAnd(
										Expression::isNull($col)
									);
								
								break;

							case self::SORT_NOT_NULL:
								
								$chain = new LogicalChain();
								
								foreach ($field as $col)
									$chain->expAnd(
										Expression::notNull($col)
									);
								
								break;
							
							default:

								throw new WrongStateException(
									'unknown or unsupported '.
									"direction '{$direction}'"
								);
						}
					} else {
						switch ($direction) {

							case self::SORT_ASC:
								$query->orderBy($field)->asc();
								break;
							
							case self::SORT_DESC:
								$query->orderBy($field)->desc();
								break;
							
							case self::SORT_IS_NULL:
								$query->orderBy(
									Expression::isNull($field)
								);
								break;
							
							case self::SORT_NOT_NULL:
								$query->orderBy(
									Expression::notNull($field)
								);
								break;

							default:
								throw new WrongStateException(
									'unknown or unsupported '.
									"direction '{$direction}'"
								);
						}
					}
				} else
					throw new WrongStateException(
						"known nothing about '{$property}' property"
					);
			}
			
			foreach ($this->logic as &$exp)
				$this->parseLogic($query, $exp, $map);

			return $query->limit($this->limit, $this->offset);
		}
		
		// recursive one
		private function parseLogic(
			SelectQuery $query, LogicalExpression $exp,
			/* array */ &$map, $onlyParse = false
		)
		{
			$left	= $exp->getLeft();
			$right	= $exp->getRight();
			$logic	= $exp->getLogic();
			
			if (
				$left instanceof LogicalExpression
				|| $right instanceof LogicalExpression
			) {
				if ($left instanceof LogicalExpression)
					$this->parseLogic($query, $left, $map, true);
				
				if ($right instanceof LogicalExpression)
					$this->parseLogic($query, $right, $map, true);
				
				$query->andWhere($exp);
				
				return $this;
			}
			
			if (
				!is_object($left) && isset($map[$left])
				&& !is_object($right) && isset($map[$right])
			) {
				if (is_array($map[$left]) && is_array($map[$right]))
					foreach ($map[$left] as $leftField)
						foreach ($map[$right] as $rightField)
							$query->andWhere(
								new LogicalExpression(
									$leftField, $rightField, $logic
								)
							);
				elseif (is_array($map[$left]))
					foreach ($map[$left] as $field)
						$query->andWhere(
							new LogicalExpression(
								$field, $right, $logic
							)
						);
				elseif (is_array($map[$right]))
					foreach ($map[$right] as $field)
						$query->andWhere(
							new LogicalExpression(
								$left, $field, $logic
							)
						);
				elseif (!$onlyParse)
					$query->andWhere($exp);
			} else {
				Assert::isFalse(
					(is_object($left) && $left instanceof SQLArray)
					&& (is_object($right) && $right instanceof SQLArray),
					
					'strange object passed'
				);
				
				if (!is_object($left)) {
					if (array_key_exists($left, $map)) {
						if ($map[$left])
							$left = new DBField($map[$left]);
						else
							$left = new DBField($left);
					} else
						$left = new DBValue($left);
				}
				
				if (!is_object($right)) {
					if (isset($map[$right]))
						$right = new DBField($map[$right]);
					elseif ($right !== null)
						$right = new DBValue($right);
				}
				
				if (!$onlyParse)
					$query->andWhere(
						new LogicalExpression($left, $right, $logic)
					);
			}
		}
		
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