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
	 * Interbase dialect.
	 *
	 * @see http://firebird.sourceforge.net/
	**/
	final class InterbaseDialect extends Dialect
	{
		public static function quoteField(&$field)
		{
			if (strpos($field, '.') !== false)
				return '"' . implode('".', explode('.', $field, 2));
			else
				return $field;
		}

		public function fullTextSearch($field, $words, $logic)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			throw new UnimplementedFeatureException();
		}
	}
?>