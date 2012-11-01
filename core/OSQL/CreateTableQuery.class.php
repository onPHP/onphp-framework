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

	final class CreateTableQuery extends QueryIdentification
	{
		private $table = null;
		
		public function __construct(DBTable $table)
		{
			$this->table = $table;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$name = $this->table->getName();
			
			$middle = "CREATE TABLE {$dialect->quoteTable($name)} (\n    ";
			
			$prepend = array();
			$columns = array();
			$primary = array();
			
			$order = $this->table->getOrder();
			
			foreach ($order as $column) {
				
				if ($column->isAutoincrement()) {
					
					if ($pre = $dialect->preAutoincrement($column))
						$prepend[] = $pre;
					
					$columns[] = implode(' ',
						array(
							$column->toDialectString($dialect),
							$dialect->postAutoincrement($column)
						)
					);
				} else
					$columns[] = $column->toDialectString($dialect);

				$name = $column->getName();
				
				if ($column->isPrimaryKey())
					$primary[] = $dialect->quoteField($name);
			}
			
			$out =
				(
					$prepend
						? implode("\n", $prepend)."\n"
						: null
				)
				.$middle
				.implode(",\n    ", $columns);
			
			if ($primary)
				$out .= ",\n    PRIMARY KEY(".implode(', ', $primary).')';
				
			if ($uniques = $this->table->getUniques()) {
				$names = array();
				
				foreach ($uniques as $row) {
					foreach ($row as $name) {
						$names[] = $dialect->quoteField($name);
					}
					
					$out .= ",\n    UNIQUE(".implode(', ', $names).')';
				}
			}
			
			return $out."\n);\n";
		}
	}
?>