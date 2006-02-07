<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
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
	 * Inexistent imaginary helper for OSQL's Query self-identification.
	 * 
	 * @ingroup DB
	**/
	final class ImaginaryDialect extends Dialect
	{
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			throw new UnsupportedMethodException();
		}
		
		public function fullTextSearch($field, $words, $logic)
		{
			return '("'.$field.'" contains "'.implode($logic, $words).'")';
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			return
				'(rank by "'.$field.'" which contains "'
					.implode($logic, $words)
				.'")';
		}
	}
?>