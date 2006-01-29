<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
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
	 * Factory for OSQL's queries.
	 * 
	 * @ingroup OSQL
	 * 
	 * @see http://onphp.org/examples/OSQL.html
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