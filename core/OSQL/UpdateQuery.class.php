<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Anton E. Lebedevich                        *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

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

		public function toString(Dialect $dialect)
		{
			$query = "UPDATE ".$dialect->quoteTable($this->table)." SET ";
			
			$sets = array();

			foreach ($this->fields as $var => $val) {
				if ($val instanceof DialectString)
					$sets[] =
						$dialect->quoteField($var)
						.' = '
						.$val->toString($dialect);
				elseif (is_null($val))
					$sets[] = $dialect->quoteField($var).' = NULL';
				else
					$sets[] =
						$dialect->quoteField($var)
						.' = '
						.$dialect->quoteValue($val);
			}
			
			$query .= implode(', ', $sets);
			
			$query .= parent::toString($dialect);

			return $query;
		}
	}
?>