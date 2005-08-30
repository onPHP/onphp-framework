<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class FromTable implements SQLTableName
	{
		private $table = null;
		private $alias = null;

		public function __construct($table, $alias = null)
		{
			if ($table instanceof SelectQuery && !$alias)
				throw new WrongArgumentException(
					"you should specify alias, when using selectQuery as table"
				);

			$this->table = $table;
			$this->alias = $alias;
		}

		public function toString(Dialect $dialect)
		{
			if ($this->table instanceof SelectQuery)
				return
					"({$this->table->toString($dialect)}) AS "
					.$dialect->quoteTable($this->alias);
			else
				return
					$dialect->quoteTable($this->table).
					(
						$this->alias
							? ' AS '.$dialect->quoteTable($this->alias)
							: ''
					);
		}

		public function getTable()
		{
			return $this->alias ? $this->alias : $this->table;
		}
	}
?>