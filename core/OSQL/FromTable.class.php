<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * SQL's "FROM"-member implementation.
	 * 
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	final class FromTable implements Aliased, SQLTableName
	{
		private $table	= null;
		private $alias	= null;
		private $schema	= null;
		
		public function __construct($table, $alias = null)
		{
			if (
				!$alias
				&&
					(
						$table instanceof SelectQuery
						|| $table instanceof LogicalObject
						|| $table instanceof SQLFunction
					)
			)
				throw new WrongArgumentException(
					'you should specify alias, when using '.
					'SelectQuery or LogicalObject as table'
				);
			
			if (is_string($table) && strpos($table, '.') !== false)
				list($this->schema, $this->table) = explode('.', $table, 2);
			else
				$this->table = $table;
			
			$this->alias = $alias;
		}
		
		public function getAlias()
		{
			return $this->alias;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (
				$this->table instanceof Query
				|| (
					$this->table instanceof SQLChain
					&& $this->table->getSize() === 1
				)
			)
				return
					"({$this->table->toDialectString($dialect)}) AS "
					.$dialect->quoteTable($this->alias);
			elseif ($this->table instanceof DialectString)
				return
					$this->table->toDialectString($dialect).' AS '
					.$dialect->quoteTable($this->alias);
			else
				return
					(
						$this->schema
							? $dialect->quoteTable($this->schema)."."
							: null
					)
					.$dialect->quoteTable($this->table)
					.(
						$this->alias
							? ' AS '.$dialect->quoteTable($this->alias)
							: null
					);
		}
		
		public function getTable()
		{
			return $this->alias ? $this->alias : $this->table;
		}
	}
?>