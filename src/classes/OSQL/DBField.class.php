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

	class DBField implements SQLExpression 
	{
		private $field	= null;
		private $table	= null;
		private $cast	= null;

		public function __construct($field, $table = null)
		{
			$this->field = $field;
			$this->table = $table;
		}
		
		public static function create($field, $table = null)
		{
			return new DBField($field, $table);
		}
		
		public function castTo($cast)
		{
			$this->cast = $cast;
			
			return $this;
		}

		public function toString(DB $db)
		{
			return
				($this->table ? $db->quoteTable($this->table).'.' : '').
				$db->quoteField($this->field).
				(null !== $this->cast ? "::{$this->cast}" : null);
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
				throw new WrongStateException('you should not override setted table');

			$this->table = $table;
			
			return $this;
		}
	}
?>