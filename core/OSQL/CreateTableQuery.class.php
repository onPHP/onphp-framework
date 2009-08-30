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
			$unique  = array();
			
			$order = $this->table->getOrder();
			
			foreach ($order as $column) {
				
				if ($column->isAutoincrement()) {

					$prepend[] = $dialect->preAutoincrement($column);
					
					$columns[] = implode(' ',
						array(
							$column->toDialectString($dialect),
							$dialect->postAutoincrement($column)
						)
					);
				} else
					$columns[] = $column->toDialectString($dialect);

				$name = $column->getName();
				
				if ($column->isUnique())
					$unique[] = $dialect->quoteField($name);
				
				if ($column->isPrimaryKey())
					$primary[] = $dialect->quoteField($name);
			}
			
			$out = implode(' ', $prepend)
				.$middle
				.implode(",\n    ", $columns);
			
			if ($primary || $unique) {
				
				if ($primary)
					$out .= ",\n    PRIMARY KEY(".implode(', ', $primary).')';
				
				if ($unique)
					$out .= ",\n    UNIQUE(".implode(', ', $unique).')';
				
			}
			
			return $out."\n);\n";
		}
	}
?>