<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Factory for OSQL's queries.
	 * 
	 * @ingroup OSQL
	 * 
	 * @see http://onphp.org/examples.OSQL.en.html
	**/
	final class OSQL extends StaticFactory
	{
		public static function select()
		{
			return new SelectQuery();
		}
		
		public static function insert()
		{
			return new InsertQuery();
		}
		
		public static function update($table = null)
		{
			return new UpdateQuery($table);
		}
		
		public static function delete()
		{
			return new DeleteQuery();
		}
		
		public static function truncate($whom = null)
		{
			return new TruncateQuery($whom);
		}
		
		public static function createTable(DBTable $table)
		{
			return new CreateTableQuery($table);
		}
		
		public static function dropTable($name, $cascade = false)
		{
			return new DropTableQuery($name, $cascade);
		}
	}
?>