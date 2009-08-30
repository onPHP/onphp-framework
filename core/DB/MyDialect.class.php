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
	 * MySQL dialect.
	 * 
	 * @see http://www.mysql.com/
	 * 
	 * @ingroup DB
	**/
	final class MyDialect extends Dialect
	{
		const IN_BOOLEAN_MODE = 1;
		
		public static function quoteValue(&$value)
		{
			/// @see Sequenceless for this convention
			
			if ($value instanceof Identifier && !$value->isFinalized())
				return "''"; // instead of 'null', to be compatible with v. 4
			
			return "'" . mysql_real_escape_string($value) . "'";
		}
		
		public static function quoteField(&$field)
		{
			if (strpos($field, '.') !== false)
				throw new WrongArgumentException();
			elseif (strpos($field, '::') !== false)
				throw new WrongArgumentException();

			return "`{$field}`";
		}
		
		public static function quoteTable(&$table)
		{
			return "`{$table}`";
		}
		
		public static function dropTableMode($cascade = false)
		{
			return null;
		}
		
		public static function timeZone($exist = false)
		{
			return null;
		}
		
		public function fullTextSearch($fields, $words, $logic)
		{
			return
				' MATCH ('
					.implode(
						', ',
						array_map(
							array($this, 'fieldToString'),
							$fields
						)
					)
					.') AGAINST ('
					.self::prepareFullText($words, $logic)
				.')';
		}
		
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			$column->setDefault(null);
			
			return 'AUTO_INCREMENT';
		}
		
		private static function prepareFullText($words, $logic)
		{
			Assert::isArray($words);
			
			$retval = self::quoteValue(implode(' ', $words));
			
			if (self::IN_BOOLEAN_MODE === $logic) {
				return addcslashes($retval, '+-<>()~*"').' '.'IN BOOLEAN MODE';
			} else {
				return $retval;
			}
		}
	}
?>