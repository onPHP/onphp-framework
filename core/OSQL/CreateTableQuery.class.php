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
		
		public function toString(Dialect $dialect)
		{
			$name = $this->table->getName();
			
			$out = "CREATE TABLE {$dialect->quoteTable($name)} (\n    ";
			
			$order = $this->table->getOrder();
			
			$columns = array();
			$primary = array();
			$unique  = array();
			
			foreach ($order as $column) {
				
				if ($column->isAutoincrement()) {
					
					$append =
						$dialect->autoincrementize($column, $prepend = null);
					
					if ($append)
						$append = ' '.$append;
					
					$columns[] = $column->toString($dialect).$append;
					
					if ($prepend)
						$out = $prepend."\n".$out;
					
				} else
					$columns[] = $column->toString($dialect);

				$name = $column->getName();
				
				if ($column->isUnique())
					$unique[] = $dialect->quoteField($name);
				
				if ($column->isPrimaryKey())
					$primary[] = $dialect->quoteField($name);
			}
			
			$out .= implode(",\n    ", $columns);
			
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