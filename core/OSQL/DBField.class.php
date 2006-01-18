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
	 * Reference for actual DB-table column.
	**/
	class DBField extends Castable implements DialectString, SQLTableName 
	{
		private $field	= null;
		private $table	= null;

		public function __construct($field, $table = null)
		{
			$this->field = $field;
			$this->table = $table;
		}
		
		public static function create($field, $table = null)
		{
			return new DBField($field, $table);
		}
		
		public function toString(Dialect $dialect)
		{
			$field =
				($this->table ? $dialect->quoteTable($this->table).'.' : null).
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

			$this->table = $table;
			
			return $this;
		}
	}
?>