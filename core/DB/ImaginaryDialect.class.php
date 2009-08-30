<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

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
					? $field->toString($this)
					: $field;
		}
		
		public function valueToString(&$value)
		{
			return
				$value instanceof DBValue
					? $value->toString($this)
					: $value;
		}

		public function fullTextSearch($field, $words, $logic)
		{
			return '("'.$this->fieldToString($field).'" contains "'.implode($logic, $words).'")';
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			return
				'(rank by "'.$this->fieldToString($field).'" which contains "'
					.implode($logic, $words)
				.'")';
		}
	}
?>