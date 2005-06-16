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

	final class DeleteQuery extends Query
	{
		private $table	= null;
		
		public function from($table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		public function toString(DB $db)
		{
			if (sizeof($this->where) > 0)
				return "DELETE FROM {$this->table} ".parent::toString($db);
			else
				throw new WrongArgumentException(
					"leave '{$this->table}' table alone in peace, bastard"
				);
		}

		public function getHash()
		{
			throw new UnsupportedMethodException();
		}
	}
?>