<?php
/****************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *   voxus@gentoo.org, noiselist@pochta.ru                                  *
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

	class GroupBy extends FieldTable {/*_*/}

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

		public function joinQuery(SelectQuery $query, LogicalObject $logic, $alias)
		{
			$this->from[] = new SQLQueryJoin($query, $logic, $alias);
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
			if ($field instanceof DBField)
				$this->group[] = new GroupBy($field);
			else 
				$this->group[] =
					new GroupBy(
						new DBField($field, $this->getLastTable($table))
					);

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
				for ($i = 0; $i < $size; $i++)
					$this->get($args[$i]);
		
			return $this;
		}
		
		public function arrayGet($array, $prefix = null)
		{
			if ($prefix)
				for ($i = 0; $i < sizeof($array); $i++)
					$this->get($array[$i], $prefix.$array[$i]);
			else
				for ($i = 0; $i < sizeof($array); $i++)
					$this->get($array[$i]);
					
			return $this;
		}

		public function getFieldsCount()
		{
			return sizeof($this->fields);
		}
		
		public function getFieldNames()
		{
			$nameList = array();
			
			foreach ($this->fields as &$field)
				$nameList[] = $field->getName();
			
			return $nameList;
		}
		
		public function toString(Dialect $dialect)
		{
			$fieldList = array();
			foreach ($this->fields as &$field) {
				
				if ($field instanceof SelectQuery) {
					
					Assert::isTrue(
						null !== $alias = $field->getName(),
						'can not use SelectQuery without name as get field'
					);
					
					$fieldList[] =
						"({$field->toString($dialect)}) AS ".
						$dialect->quoteField($alias);
				} else
					$fieldList[] = $field->toString($dialect);
			}

			$query = 
				'SELECT '.($this->distinct ? 'DISTINCT ' : '').
				implode(', ', $fieldList);
				
			$fromString = "";
			for ($i = 0; $i < sizeof($this->from); $i++) {
				if ($i == 0)
					$separator = '';
				elseif (
					$this->from[$i] instanceof FromTable &&
					!$this->from[$i]->getTable() instanceof SelectQuery
				)
					$separator = ', ';
				else
					$separator = ' ';

				$fromString .= $separator.$this->from[$i]->toString($dialect);
			}

			if ($fromString)
				$query .= ' FROM '.$fromString;

			// WHERE
			$query .= parent::toString($dialect);

			/* GROUP */ {
				$groupList = array();

				foreach ($this->group as $group)
					$groupList[] = $group->toString($dialect);

				if (sizeof($groupList))
					$query .= " GROUP BY ".implode(', ', $groupList);
			}

			/* ORDER */ {
				$orderList = array();

				foreach($this->order as $order)
					$orderList[] = $order->toString($dialect);

				if (sizeof($orderList))
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
			if (!$table && sizeof($this->from))
				return $this->from[sizeof($this->from) - 1]->getTable();
			else 
				return $table;
		}
	}
?>