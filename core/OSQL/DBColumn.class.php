<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class DBColumn implements SQLTableName // also abused in FromTable
	{
		private $type		= null;
		private $name		= null;
		
		private $table		= null;
		private $reference	= null;
		
		private $primary	= null;
		private $unique		= null;
		
		public static function create(DataType $type, $name)
		{
			return new DBColumn($type, $name);
		}
		
		public function __construct(DataType $type, $name)
		{
			$this->type = $type;
			$this->name = $name;
		}
		
		public function setTable(DBTable $table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getTable()
		{
			return $this->table;
		}
		
		public function isPrimaryKey()
		{
			return $this->primary;
		}
		
		public function setPrimaryKey($primary = false)
		{
			$this->primary = true === $primary;
			
			return $this;
		}
		
		public function isUnique()
		{
			return $this->unique;
		}
		
		public function setUnique($unique = false)
		{
			$this->unique = true === $unique;
			
			return $this;
		}
		
		public function toString(Dialect $dialect)
		{
			return
				"{$dialect->quoteField($this->name)} "
				.$this->type->toString($dialect);
		}
	}
?>