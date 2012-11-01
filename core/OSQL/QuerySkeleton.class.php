<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	namespace Onphp;

	abstract class QuerySkeleton extends QueryIdentification
	{
		protected $where		= array();	// where clauses
		protected $whereLogic	= array();	// logic between where's
		protected $aliases		= array();
		protected $returning 	= array();
		
		public function getWhere()
		{
			return $this->where;
		}
		
		public function getWhereLogic()
		{
			return $this->whereLogic;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\QuerySkeleton
		**/
		public function where(LogicalObject $exp, $logic = null)
		{
			if ($this->where && !$logic)
				throw new WrongArgumentException(
					'you have to specify expression logic'
				);
			else {
				if (!$this->where && $logic)
					$logic = null;
				
				$this->whereLogic[] = $logic;
				$this->where[] = $exp;
			}
			
			return $this;
		}
		
		/**
		 * @return \Onphp\QuerySkeleton
		**/
		public function andWhere(LogicalObject $exp)
		{
			return $this->where($exp, 'AND');
		}
		
		/**
		 * @return \Onphp\QuerySkeleton
		**/
		public function orWhere(LogicalObject $exp)
		{
			return $this->where($exp, 'OR');
		}
		
		/**
		 * @return \Onphp\QuerySkeleton
		**/
		public function returning($field, $alias = null)
		{
			$this->returning[] =
				$this->resolveSelectField(
					$field,
					$alias,
					$this->table
				);
			
			if ($alias = $this->resolveAliasByField($field, $alias)) {
				$this->aliases[$alias] = true;
			}
			
			return $this;
		}
		
		/**
		 * @return \Onphp\QuerySkeleton
		**/
		public function dropReturning()
		{
			$this->returning = array();
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if ($this->where) {
				$clause = ' WHERE';
				$outputLogic = false;
				
				for ($i = 0, $size = count($this->where); $i < $size; ++$i) {
					
					if ($exp = $this->where[$i]->toDialectString($dialect)) {
						
						$clause .= "{$this->whereLogic[$i]} {$exp} ";
						$outputLogic = true;
						
					} elseif (!$outputLogic && isset($this->whereLogic[$i + 1]))
						$this->whereLogic[$i + 1] = null;
					
				}
				
				return rtrim($clause, ' ');
			}
			
			return null;
		}
		
		protected function resolveSelectField($field, $alias, $table)
		{
			if (is_object($field)) {
				if (
					($field instanceof DBField)
					&& ($field->getTable() === null)
				) {
					$result = new SelectField(
						$field->setTable($table),
						$alias
					);
				} elseif ($field instanceof SelectQuery) {
					$result = $field;
				} elseif ($field instanceof DialectString) {
					$result = new SelectField($field, $alias);
				} else
					throw new WrongArgumentException('unknown field type');
				
				return $result;
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
			
			$result = new SelectField(
				new DBField($fieldName, $table), $alias
			);
			
			return $result;
		}
		
		protected function resolveAliasByField($field, $alias)
		{
			if (is_object($field)) {
				if (
					($field instanceof DBField)
					&& ($field->getTable() === null)
				) {
					return null;
				}
				
				if (
					$field instanceof SelectQuery
					|| ($field instanceof DialectString	&& $field instanceof Aliased)
				) {
					return $field->getAlias();
				}
			}
			
			return $alias;
		}
		
		/**
		 * @return \Onphp\QuerySkeleton
		**/
		protected function checkReturning(Dialect $dialect)
		{
			if (
				$this->returning
				&& !$dialect->hasReturning()
			) {
				throw new UnimplementedFeatureException();
			}
			
			return $this;
		}
		
		protected function toDialectStringField($field, Dialect $dialect)
		{
			if ($field instanceof SelectQuery) {
				Assert::isTrue(
					null !== $alias = $field->getName(),
					'can not use SelectQuery to table without name as get field: '
					.$field->toDialectString(ImaginaryDialect::me())
				);
				
				return
					"({$field->toDialectString($dialect)}) AS ".
					$dialect->quoteField($alias);
			} else
				return $field->toDialectString($dialect);
		}
		
		protected function toDialectStringReturning(Dialect $dialect)
		{
			$fields = array();
			
			foreach ($this->returning as $field)
				$fields[] = $this->toDialectStringField($field, $dialect);
			
			return implode(', ', $fields);
		}
	}
?>