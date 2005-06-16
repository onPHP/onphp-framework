<?php
/***************************************************************************
 *   Copyright (C) 2004 by Anton Lebedevich                                *
 *   support@rabota.ru                                                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class UpdateQuery extends InsertOrUpdateQuery
	{
		public function __construct($table)
		{
			$this->table = $table;
		}

		public function toString(DB $db)
		{
			$query = "UPDATE ".$db->quoteTable($this->table)." SET ";
			
			$sets = array();

			foreach ($this->fields as $var => $val) {
				if (is_null($val))
					$sets[] = $db->quoteField($var).' = NULL';
				else
					$sets[] = $db->quoteField($var).' = '.$db->quoteValue($val);
			}
			
			$query .= implode(', ',$sets);
			
			$query .= parent::toString($db);

			return $query;
		}
	}
?>