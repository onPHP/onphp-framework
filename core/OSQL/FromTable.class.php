<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * SQL's "FROM"-member implementation.
	**/
	class FromTable implements SQLTableName
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
					)
			)
				throw new WrongArgumentException(
					'you should specify alias, when using '.
					'SelectQuery or LogicalObject as table'
				);

			if (is_string($table) && strpos($table, '.'))
				list($this->schema, $this->table) = explode('.', $table, 2);
			else
				$this->table = $table;
			
			$this->alias = $alias;
		}

		public function toString(Dialect $dialect)
		{
			if (
				$this->table instanceof SelectQuery
				|| $this->table instanceof LogicalObject
			)
				return
					"({$this->table->toString($dialect)}) AS "
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