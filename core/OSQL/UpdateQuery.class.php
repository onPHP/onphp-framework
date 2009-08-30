<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	final class UpdateQuery extends InsertOrUpdateQuery
	{
		public function __construct($table = null)
		{
			$this->table = $table;
		}
		
		public function setTable($table)
		{
			$this->table = $table;
			
			return $this;
		}

		public function toDialectString(Dialect $dialect)
		{
			$query = "UPDATE ".$dialect->quoteTable($this->table)." SET ";
			
			$sets = array();

			foreach ($this->fields as $var => $val) {
				if ($val instanceof DialectString)
					$sets[] =
						$dialect->quoteField($var)
						.' = ('
						.$val->toDialectString($dialect)
						.')';
				elseif ($val === null)
					$sets[] = $dialect->quoteField($var).' = NULL';
				elseif (true === $val)
					$sets[] = $dialect->quoteField($var).' = TRUE';
				elseif (false === $val)
					$sets[] = $dialect->quoteField($var).' = FALSE';
				else
					$sets[] =
						$dialect->quoteField($var)
						.' = '
						.$dialect->quoteValue($val);
			}
			
			$query .= implode(', ', $sets);
			
			$query .= parent::toDialectString($dialect);

			return $query;
		}
	}
?>