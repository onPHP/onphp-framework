<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Reference for actual DB-table column.
	 * 
	 * @ingroup OSQL
	**/
	class DBField extends Castable implements DialectString, SQLTableName
	{
		private $field	= null;
		private $table	= null;
		
		public function __construct($field, $table = null)
		{
			$this->field = $field;
			
			if ($table)
				$this->setTable($table);
		}
		
		public static function create($field, $table = null)
		{
			return new DBField($field, $table);
		}
		
		public function toString(Dialect $dialect)
		{
			$field =
				($this->table ? $this->table->toString($dialect).'.' : null).
				$dialect->quoteField($this->field);
			
			return
				$this->cast
					? $dialect->toCasted($field, $this->cast)
					: $field;
		}
		
		public function getField()
		{
			return $this->field;
		}

		public function getTable()
		{
			return $this->table;
		}
		
		public function setTable($table)
		{
			if ($this->table !== null)
				throw new WrongStateException(
					'you should not override setted table'
				);
			
			if (!$table instanceof DialectString)
				$this->table = new FromTable($table);
			else
				$this->table = $table;
			
			return $this;
		}
	}
?>