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

	final class MyDialect extends Dialect
	{
		public static function quoteValue(&$value)
		{
			return "'" . mysql_real_escape_string($value) . "'";
		}
		
		public static function quoteField(&$field)
		{
			if (strpos($field, '.') !== false)
				throw new WrongArgumentException();
			elseif (strpos($field, '::') !== false)
				throw new WrongArgumentException();

			return "`$field`";
		}
		
		public static function quoteTable(&$table)
		{
			return "`$table`";
		}
		
		public function fullTextSearch($field, $words, $logic)
		{
			throw new UnimplementedFeatureException('implement me first!');
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			throw new UnimplementedFeatureException('implement me first!');
		}
	}
?>