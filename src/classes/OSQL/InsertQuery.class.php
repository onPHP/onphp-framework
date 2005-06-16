<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class InsertQuery extends InsertOrUpdateQuery
	{
		public function into($table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		public function toString(DB $db)
		{
			$query = "INSERT INTO ".$db->quoteTable($this->table)." ";
			
			$fields = '';
			$values = '';
			
			foreach ($this->fields as $var => $val) {
				$fields[] = $db->quoteField($var);
				if (is_null($val))
					$values[] = 'NULL';
				else
					$values[] = $db->quoteValue($val);
			}
			
			$fields = implode(', ', $fields);
			$values = implode(', ', $values);
			
			$query .= "({$fields}) VALUES ({$values})";
			
			return $query;
		}

		public function where(LogicalObject $exp, $logic = null)
		{
			throw new UnsupportedMethodException();
		}
		
		public function andWhere(LogicalObject $exp)
		{
			throw new UnsupportedMethodException();
		}
		
		public function orWhere(LogicalObject $exp)
		{
			throw new UnsupportedMethodException();
		}
	}
?>