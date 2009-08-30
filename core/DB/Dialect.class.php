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
	 * Base (aka ANSI) SQL dialect.
	 * 
	 * @ingroup DB
	**/
	abstract class /* ANSI's */ Dialect
	{
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			throw new UnimplementedFeatureException('boo');
		}
		
		public static function quoteValue(&$value)
		{
			return
				(
					(
						is_numeric($value)
						// to avoid values like '108E102'
						// (is_numeric()'ll return true)
						&& $value == (int) $value
						&& strlen($value) == strlen((int) $value)
					) ||
					(is_string($value) && strtolower($value) == 'null')
				)
					? $value
					: "'".addslashes($value)."'";
		}
		
		public static function quoteField(&$field)
		{
			return self::quoteTable($field);
		}
		
		public static function quoteTable(&$table)
		{
			return '"'.$table.'"';
		}

		public static function toCasted($field, $type)
		{
			return "CAST ({$field} AS {$type})";
		}
		
		public static function timeZone($exist = false)
		{
			return
				$exist
					? ' WITH TIME ZONE'
					: ' WITHOUT TIME ZONE';
		}
		
		public static function dropTableMode($cascade = false)
		{
			return
				$cascade
					? ' CASCADE'
					: ' RESTRICT';
		}
		
		public function fieldToString(&$field)
		{
			return
				$field instanceof DialectString
					? $field->toString($this)
					: $this->quoteField($field);
		}
		
		public function valueToString(&$value)
		{
			return
				$value instanceof DBValue
					? $value->toString($this)
					: $this->quoteValue($value);
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