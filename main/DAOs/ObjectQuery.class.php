<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class ObjectQuery
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
			return new ObjectQuery();
		}

		public function sort($name)
		{
			if ($this->current)
				$this->sort[$this->current] = self::SORT_ASC;

			$this->current = $name;		

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
		
		public function setLimit($limit)
		{
			$this->limit = $limit;
			
			return $this;
		}
		
		public function setOffset($offset)
		{
			$this->offset = $offset;
			
			return $this;
		}
		
		public function addLogic(LogicalExpression $exp)
		{
			$this->logic[] = $exp;
			
			return $this;
		}
		
		public function toSelectQuery(MappedDAO $dao)
		{
			// cleanup
			if ($this->current) {
				$this->sort[$this->current] = self::SORT_ASC;
				$this->current = null;
			}
			
			$map = $dao->getMapping();

			$query = $dao->makeSelectHead();
			
			foreach ($this->sort as $property => $direction) {
				if (isset($map[$property])) {
					
					$field = &$map[$property];
					
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

								throw new StateExpression('unknown direction');
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
								throw new StateExpression('unknown direction');
						}
					}
				} else
					throw new WrongStateException(
						'known nothing about such property'
					);
			}
			
			foreach ($this->logic as &$exp) {
				
				$left	= $exp->getLeft();
				$right	= $exp->getRight();
				$logic	= $exp->getLogic();
				
				if (
					isset($map[$left])
					&& isset($map[$right])
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
					else
						$query->andWhere($exp);
				}
			}

			return $query->limit($this->limit, $this->offset);
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