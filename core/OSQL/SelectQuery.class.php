<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	final class SelectQuery
		extends QuerySkeleton
		implements Named, JoinCapableQuery, Aliased
	{
		private $distinct		= false;
		
		private $name			= null;
		
		private $joiner			= null;
		
		private $limit			= null;
		private $offset			= null;
		
		private $fields			= array();
		
		private $order			= null;
		
		private $group			= array();
		
		private $having			= null;
		
		public function __construct()
		{
			$this->joiner = new Joiner();
			$this->order = new OrderChain();
		}
		
		public function __clone()
		{
			$this->joiner = clone $this->joiner;
			$this->order = clone $this->order;
		}
		
		public function hasAliasInside($alias)
		{
			return isset($this->aliases[$alias]);
		}
		
		public function getAlias()
		{
			return $this->name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function setName($name)
		{
			$this->name = $name;
			$this->aliases[$name] = true;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function distinct()
		{
			$this->distinct = true;
			return $this;
		}
		
		public function isDistinct()
		{
			return $this->distinct;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function unDistinct()
		{
			$this->distinct = false;
			return $this;
		}
		
		public function hasJoinedTable($table)
		{
			return $this->joiner->hasJoinedTable($table);
		}
		
		public function getJoinedTables()
		{
			return $this->joiner->getTables();
		}
		
		/**
		 * @return SelectQuery
		**/
		public function join($table, LogicalObject $logic, $alias = null)
		{
			$this->joiner->join(new SQLJoin($table, $logic, $alias));
			$this->aliases[$alias] = true;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function leftJoin($table, LogicalObject $logic, $alias = null)
		{
			$this->joiner->leftJoin(new SQLLeftJoin($table, $logic, $alias));
			$this->aliases[$alias] = true;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function rightJoin($table, LogicalObject $logic, $alias = null)
		{
			$this->joiner->rightJoin(new SQLRightJoin($table, $logic, $alias));
			$this->aliases[$alias] = true;
			
			return $this;
		}

		/**
		 * @param $table
		 * @param LogicalObject $logic
		 * @param null $alias
		 * @return SelectQuery
		 */
		public function fullOuterJoin($table, LogicalObject $logic, $alias = null)
		{
			$this->joiner->fullOuterJoin(
				new SQLFullOuterJoin($table, $logic, $alias)
			);

			$this->aliases[$alias] = true;

			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function setOrderChain(OrderChain $chain)
		{
			$this->order = $chain;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function orderBy($field, $table = null)
		{
			$this->order->add($this->makeOrder($field, $table));
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function prependOrderBy($field, $table = null)
		{
			$this->order->prepend($this->makeOrder($field, $table));
			
			return $this;
		}
		
		/**
		 * @throws WrongStateException
		 * @return SelectQuery
		**/
		public function desc()
		{
			if (!$last = $this->order->getLast())
				throw new WrongStateException('no fields to sort');
			
			$last->desc();
			
			return $this;
		}
		
		/**
		 * @throws WrongStateException
		 * @return SelectQuery
		**/
		public function asc()
		{
			if (!$last = $this->order->getLast())
				throw new WrongStateException('no fields to sort');
			
			$last->asc();
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function groupBy($field, $table = null)
		{
			if ($field instanceof DialectString)
				$this->group[] = $field;
			else
				$this->group[] =
					new DBField($field, $this->getLastTable($table));
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function dropGroupBy()
		{
			$this->group = array();
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function having(LogicalObject $exp)
		{
			$this->having = $exp;
			
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
		
		/**
		 * @throws WrongArgumentException
		 * @return SelectQuery
		**/
		public function limit($limit = null, $offset = null)
		{
			if ($limit !== null)
				Assert::isPositiveInteger($limit, 'invalid limit specified');
				
			if ($offset !== null)
				Assert::isInteger($offset, 'invalid offset specified');
			
			$this->limit = $limit;
			$this->offset = $offset;
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function from($table, $alias = null)
		{
			$this->joiner->from(new FromTable($table, $alias));
			
			$this->aliases[$alias] = true;
			
			return $this;
		}
		
		public function getFirstTable()
		{
			return $this->joiner->getFirstTable();
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return SelectQuery
		**/
		public function get($field, $alias = null)
		{
			$this->fields[] =
				$this->resolveSelectField(
					$field,
					$alias,
					$this->getLastTable()
				);
			
			if ($alias = $this->resolveAliasByField($field, $alias)) {
				$this->aliases[$alias] = true;
			}
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function multiGet(/* ... */)
		{
			$size = func_num_args();
		
			if ($size && $args = func_get_args())
				for ($i = 0; $i < $size; ++$i)
					$this->get($args[$i]);
		
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function arrayGet($array, $prefix = null)
		{
			$size = count($array);
			
			if ($prefix) {
				for ($i = 0; $i < $size; ++$i) {
					if ($array[$i] instanceof DialectString) {
						if ($array[$i] instanceof DBField) {
							$alias = $prefix.$array[$i]->getField();
						} else {
							if ($array[$i] instanceof SQLFunction) {
								$alias =
									$array[$i]->setAlias(
										$prefix.$array[$i]->getName()
									)->
									getAlias();
							} else {
								$alias = $array[$i];
							}
						}
					} else {
						$alias = $prefix.$array[$i];
					}
					
					$this->get($array[$i], $alias);
				}
			} else {
				for ($i = 0; $i < $size; ++$i) {
					$this->get($array[$i]);
				}
			}
			
			return $this;
		}
		
		public function getFieldsCount()
		{
			return count($this->fields);
		}
		
		public function getTablesCount()
		{
			return $this->joiner->getTablesCount();
		}
		
		public function getFieldNames()
		{
			$nameList = array();
			
			foreach ($this->fields as $field) {
				if ($field instanceof SelectField) {
					if ($alias = $field->getAlias()) {
						$nameList[] = $alias;
						continue;
					} elseif (($subField = $field->getField()) instanceof Aliased) {
						if ($alias = $subField->getAlias()) {
							$nameList[] = $alias;
							continue;
						}
					}
				}
				
				$nameList[] = $field->getName();
			}
			
			return $nameList;
		}
		
		public function returning($field, $alias = null)
		{
			throw new UnsupportedMethodException();
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$fieldList = array();
			
			foreach ($this->fields as $field)
				$fieldList[] = $this->toDialectStringField($field, $dialect);
			
			$query =
				'SELECT '.($this->distinct ? 'DISTINCT ' : null)
				.implode(', ', $fieldList)
				.$this->joiner->toDialectString($dialect);
				
			// WHERE
			$query .= parent::toDialectString($dialect);
			
			if ($this->group) {
				$groupList = array();
				
				foreach ($this->group as $group)
					$groupList[] = $group->toDialectString($dialect);
				
				if ($groupList)
					$query .= ' GROUP BY '.implode(', ', $groupList);
			}
			
			if ($this->having)
				$query .= ' HAVING '.$this->having->toDialectString($dialect);
			
			if ($this->order->getCount()) {
				$query .= ' ORDER BY '.$this->order->toDialectString($dialect);
			}
			
			if ($this->limit)
				$query .= ' LIMIT '.$this->limit;
			
			if ($this->offset)
				$query .= ' OFFSET '.$this->offset;
			
			return $query;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function dropFields()
		{
			$this->fields = array();
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function dropOrder()
		{
			$this->order = new OrderChain();
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function dropLimit()
		{
			$this->limit = $this->offset = null;
			return $this;
		}
		
		private function getLastTable($table = null)
		{
			if (!$table && ($last = $this->joiner->getLastTable()))
				return $last;
			
			return $table;
		}
		
		/**
		 * @return OrderBy
		**/
		private function makeOrder($field, $table = null)
		{
			if (
				$field instanceof OrderBy
				|| $field instanceof DialectString
			)
				return $field;
			else
				return
					new OrderBy(
						new DBField($field, $this->getLastTable($table))
					);
		}
	}
?>