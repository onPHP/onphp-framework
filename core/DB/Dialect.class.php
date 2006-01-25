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
	 * Base (aka ANSI) SQL dialect.
	 * 
	 * @ingroup DB
	**/
	abstract class /* ANSI's */ Dialect
	{
		abstract public function fullTextSearch($field, $words, $logic);
		abstract public function fullTextRank($field, $words, $logic);
		
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
			if (strpos($table, '.') !== false)
				return '"' . implode('"."', explode('.', $table, 2)) . '"';
			else
				return '"'.$table.'"';
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
					: $this->quoteField($field);
		}
		
		public function valueToString(&$value)
		{
			return
				$value instanceof DBValue
					? $value->toString($this)
					: $this->quoteValue($value);
		}
	}
?>