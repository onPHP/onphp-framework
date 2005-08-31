<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov, Anton Lebedevich        *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class GroupBy extends FieldTable {/*_*/}

	/**
	 * mostly for write-only methods (setters)
	**/
	abstract class SelectQuerySkeleton extends QuerySkeleton
	{
		protected $distinct			= false;

		protected $limit			= null;
		protected $offset			= null;

		protected $fields			= array();
		protected $from				= array();
		protected $order			= array();
		protected $group			= array();

		public function distinct()
		{
			$this->distinct = true;
			return $this;
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

		public function limit($limit = null, $offset = null)
		{
			$this->limit = $limit;
			$this->offset = $offset;
			return $this;
		}

		public function orderBy($field, $table = null)
		{
			if ($field instanceof DialectString)
				$this->order[] = new OrderBy($field);
			else
				$this->order[] =
					new OrderBy(
						new DBField($field, $this->getLastTable($table))
					);

			return $this;
		}

		public function desc()
		{
			if (!sizeof($this->order))
				throw new WrongStateException("no fields to sort");

			$this->order[sizeof($this->order) - 1]->desc();

			return $this;
		}
		
		public function asc()
		{
			if (!sizeof($this->order))
				throw new WrongStateException("no fields to sort");

			$this->order[sizeof($this->order) - 1]->asc();

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

		public function multiGet()
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
		
		public function getCount($field, $alias = null)
		{
			return $this->getFunction('COUNT', $field, $alias);
		}
		
		// wrapper for backward compatibility
		public function getFunction($function, $field, $alias = null)
		{
			if (!$field instanceof DBField)
				$field = new DBField($field, $this->getLastTable());
			
			$this->fields[] =
				SQLFunction::create($function, $field)->setAlias($alias);
			
			return $this;
		}
	}
?>