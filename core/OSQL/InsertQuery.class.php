<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
	final class InsertQuery extends InsertOrUpdateQuery
	{
		public function into($table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		// just alias to behave like UpdateQuery
		public function setTable($table)
		{
			return $this->into($table);
		}
		
		public function toString(Dialect $dialect)
		{
			$query = "INSERT INTO ".$dialect->quoteTable($this->table)." ";
			
			$fields = array();
			$values = array();
			
			foreach ($this->fields as $var => $val) {
				$fields[] = $dialect->quoteField($var);
				
				if (is_null($val))
					$values[] = 'NULL';
				elseif (true === $val)
					$values[] = 'TRUE';
				elseif (false === $val)
					$values[] = 'FALSE';
				else
					$values[] = $dialect->quoteValue($val);
			}
			
			if (!$fields || !$values)
				throw new WrongStateException('what should i insert?');
			
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