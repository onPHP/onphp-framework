<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
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
			return 'AUTOINCREMENT';
		}
		
		public static function quoteValue(&$value)
		{
			return $value;
		}
		
		public static function quoteField(&$field)
		{
			return $field;
		}
		
		public static function quoteTable(&$table)
		{
			return $table;
		}

		public static function toCasted($field, $type)
		{
			return "CAST ({$field} AS {$type})";
		}
		
		public function fieldToString(&$field)
		{
			return
				$field instanceof DialectString
					? $field->toDialectString($this)
					: $field;
		}
		
		public function valueToString(&$value)
		{
			return
				$value instanceof DBValue
					? $value->toDialectString($this)
					: $value;
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