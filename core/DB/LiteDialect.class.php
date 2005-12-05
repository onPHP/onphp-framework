<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * SQLite dialect.
	 *
	 * @see http://www.sqlite.org/
	**/
	final class LiteDialect extends Dialect
	{
		public static function quoteValue(&$value)
		{
			/// @see Sequenceless for this convention
			
			if ($value instanceof Identifier && !$value->isFinalized())
				return 'null';
			
			return "'" .sqlite_escape_string($value)."'";
		}

		public function fullTextSearch($field, $words, $logic)
		{
			throw new UnsupportedMethodException();
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			throw new UnsupportedMethodException();
		}
	}
?>