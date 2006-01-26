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
			
			$out = "CREATE TABLE {$dialect->quoteTable($name)} (\n";
			
			$order = $this->table->getOrder();
			
			$columns = array();
			
			foreach ($order as $name) {
				$columns[] = $this->table->getColumnByName($name)->toString($dialect);
			}
			
			$out .= implode(",\n", $columns);
			
			return $out."\n);\n";
		}
	}
?>