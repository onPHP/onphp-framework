<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

/*
	$Id$
	
	06 Oct 2005: Skeleton merged back.
	
	07 Jun 2005: Separation of {get,set}ters.
	
	28 Mar 2005: Fourth rewrite by Anton.
	
	04 Jan 2005: Third rewrite. Main goal now - simplicity.
*/

	/**
	 * @ingroup OSQL
	**/
	final class SelectQuery extends QuerySkeleton implements Named
	{
		private $distinct		= false;

		private $name			= null;
		
		private $limit			= null;
		private $offset			= null;

		private $fields			= array();
		private $from			= array();
		
		private $currentOrder	= null;
		private $order			= array();
		
		private $group			= array();
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}

		public function distinct()
		{
			$this->distinct = true;
			return $this;
		}

		public function isDistinct()
		{
			return $this->distinct;
		}

		public function unDistinct()
		{
			$this->distinct = false;
			return $this;
		}

		/// @deprecated by join()
		public function joinQuery(SelectQuery $query, LogicalObject $logic, $alias)
		{
			$this->from[] = new SQLJoin($query, $logic, $alias);
			return $this;
		}
		
		public function join($table, LogicalObject $logic, $alias = null)
		{
			$this->from[] = new SQLJoin($table, $logic, $alias);
			return $this;
		}
		
		public function leftJoin($table, LogicalObject $logic, $alias = null)
		{
			$this->from[] = new SQLLeftJoin($table, $logic, $alias);
			return $this;
		}

		public function orderBy($field, $table = null)
		{
			if ($field instanceof DialectString)
				$order = new OrderBy($field);
			else
				$order =
					new OrderBy(
						new DBField($field, $this->getLastTable($table))
					);

			$this->order[] = $order;
			$this->currentOrder = &$order;
			
			return $this;
		}
		
		public function prependOrderBy($field, $table = null)
		{
			if ($field instanceof DialectString)
				$order = new OrderBy($field);
			else
				$order =
					new OrderBy(
						new DBField($field, $this->getLastTable($table))
					);
			
			if ($this->order)
				array_unshift($this->order, $order);
			else
				$this->order[] = $order;
			
			$this->currentOrder = &$order;

			return $this;
		}

		public function desc()
		{
			if (!$this->currentOrder)
				throw new WrongStateException("no fields to sort");

			$this->currentOrder->desc();

			return $this;
		}
		
		public function asc()
		{
			if (!$this->currentOrder)
				throw new WrongStateException("no fields to sort");

			$this->currentOrder->asc();

			return $this;
		}

		public function groupBy($field, $table = null)
		{
			if ($field instanceof DialectString)
				$this->group[] = $field;
			else 
				$this->group[] =
						new DBField($field, $this->getLastTable($table));

			return $this;
		}

		public function getLimit()
		{
			return $this->limit;
		}
		
		public function getOffset()
		{
			return $this->offset;
		}

		public function limit($limit = null, $offset = null)
		{
			if ($limit !== null) 
				Assert::isInteger($limit, 'invalid limit specified');
				
			if ($offset !== null)
				Assert::isInteger($offset, 'invalid offset specified');
			
			$this->limit = $limit;
			$this->offset = $offset;
			
			return $this;
		}

		public function from($table, $alias = null)
		{
			$this->from[] = new FromTable($table, $alias);

			return $this;
		}
		
		// BOVM: achtung!
		public function get($field, $alias = null)
		{
			$table = null;
			if (is_object($field)) {
				if ($field instanceof DBField) {
					if ($field->getTable() === null)
						$this->fields[] = new SelectField(
							$field->setTable($this->getLastTable()),
							$alias
						);
					else
						$this->fields[] = new SelectField($field, $alias);

					return $this;
				} elseif ($field instanceof DialectString) {
					$this->fields[] = $field;
					
					return $this;
				} else
					throw new WrongArgumentException('unknown field type');

			} elseif (false !== strpos($field, '*'))
				throw new WrongArgumentException(
					'do not fsck with us: specify fields explicitly'
				);
			elseif (false !== strpos($field, '.'))
				throw new WrongArgumentException(
					'forget about dot: use DBField'
				);
			else
				$fieldName = $field;
				
			$this->fields[] = new SelectField(
				new DBField($fieldName, $this->getLastTable($table)), $alias
			);

			return $this;
		}

		public function multiGet(/* ... */)
		{
			$size = func_num_args();
		
			if ($size && $args = func_get_args())
				for ($i = 0; $i < $size; ++$i)
					$this->get($args[$i]);
		
			return $this;
		}
		
		public function arrayGet($array, $prefix = null)
		{
			$size = count($array);
			
			if ($prefix)
				for ($i = 0; $i < $size; ++$i)
					$this->get(
						$array[$i],
						$array[$i] instanceof DialectString
							? $array[$i] instanceof DBField
								? $prefix.$array[$i]->getField()
								: $array[$i] instanceof SQLFunction
									?
										$array[$i]->setAlias(
											$prefix.$array[$i]->getName()
										)
									: $array[$i]
							: $prefix.$array[$i]
					);
			else
				for ($i = 0; $i < $size; ++$i)
					$this->get($array[$i]);
					
			return $this;
		}

		public function getFieldsCount()
		{
			return count($this->fields);
		}
		
		public function getFieldNames()
		{
			$nameList = array();
			
			foreach ($this->fields as &$field)
				$nameList[] = $field->getName();
			
			return $nameList;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$fieldList = array();
			foreach ($this->fields as &$field) {
				
				if ($field instanceof SelectQuery) {
					
					Assert::isTrue(
						null !== $alias = $field->getName(),
						'can not use SelectQuery without name as get field'
					);
					
					$fieldList[] =
						"({$field->toDialectString($dialect)}) AS ".
						$dialect->quoteField($alias);
				} else
					$fieldList[] = $field->toDialectString($dialect);
			}

			$query = 
				'SELECT '.($this->distinct ? 'DISTINCT ' : null).
				implode(', ', $fieldList);
				
			$fromString = null;
			
			for ($i = 0, $size = count($this->from); $i < $size; ++$i) {
				if ($i == 0)
					$separator = null;
				elseif (
					$this->from[$i] instanceof FromTable &&
					!$this->from[$i]->getTable() instanceof SelectQuery
				)
					$separator = ', ';
				else
					$separator = ' ';

				$fromString .= $separator.$this->from[$i]->toDialectString($dialect);
			}

			if ($fromString)
				$query .= ' FROM '.$fromString;

			// WHERE
			$query .= parent::toDialectString($dialect);

			if ($this->group) {
				$groupList = array();
				
				foreach ($this->group as $group)
					$groupList[] = $group->toDialectString($dialect);
				
				if ($groupList)
					$query .= " GROUP BY ".implode(', ', $groupList);
			}

			if ($this->order) {
				$orderList = array();

				foreach ($this->order as $order)
					$orderList[] = $order->toDialectString($dialect);

				if ($orderList)
					$query .= " ORDER BY ".implode(', ', $orderList);
			}
	
			if ($this->limit)
				$query .= " LIMIT {$this->limit}";
			
			if ($this->offset)
				$query .= " OFFSET {$this->offset}";
	
			return $query;
		}
		
		public function dropFields()
		{
			$this->fields = array();
			return $this;
		}
		
		public function dropOrder()
		{
			$this->order = array();
			return $this;
		}

		private function getLastTable($table = null)
		{
			if (!$table && $this->from)
				return $this->from[count($this->from) - 1]->getTable();
			else 
				return $table;
		}
	}
?>