<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	final class DBSchema extends QueryIdentification
	{
		private $tables = array();
		
		public function getTables()
		{
			return $this->tables;
		}
		
		public function getTableNames()
		{
			return array_keys($this->tables);
		}
		
		public function addTable(DBTable $table)
		{
			$name = $table->getName();
			
			Assert::isFalse(
				isset($this->tables[$name]),
				"table '{$name}' already exist"
			);
			
			$this->tables[$table->getName()] = $table;
			
			return $this;
		}
		
		public function getTableByName($name)
		{
			Assert::isTrue(
				isset($this->tables[$name]),
				"table '{$name}' does not exist"
			);
			
			return $this->tables[$name];
		}
		
		// TODO: respect dependency order
		public function toString(Dialect $dialect)
		{
			$out = array();
			
			foreach ($this->tables as $name => $table) {
				$out[] = $table->toString($dialect);
			}
			
			return implode("\n\n", $out);
		}
	}
?>