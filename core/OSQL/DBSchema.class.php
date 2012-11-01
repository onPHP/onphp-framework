<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	namespace Onphp;

	final class DBSchema extends QueryIdentification
	{
		private $tables	= array();
		private $order	= array();
		
		public function getTables()
		{
			return $this->tables;
		}
		
		public function getTableNames()
		{
			return $this->order;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\DBSchema
		**/
		public function addTable(DBTable $table)
		{
			$name = $table->getName();
			
			Assert::isFalse(
				isset($this->tables[$name]),
				"table '{$name}' already exist"
			);
			
			$this->tables[$table->getName()] = $table;
			$this->order[] = $name;
			
			return $this;
		}
		
		/**
		 * @throws \Onphp\MissingElementException
		 * @return \Onphp\DBTable
		**/
		public function getTableByName($name)
		{
			if (!isset($this->tables[$name]))
				throw new MissingElementException(
					"table '{$name}' does not exist"
				);
			
			return $this->tables[$name];
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$out = array();
			
			foreach ($this->order as $name) {
				$out[] = $this->tables[$name]->toDialectString($dialect);
			}
			
			return implode("\n\n", $out);
		}
	}
?>