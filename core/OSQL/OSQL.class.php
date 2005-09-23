<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class OSQL
	{
		private function __construct() {}
		
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
	}
?>