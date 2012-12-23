<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * MySQL dialect.
	 *
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysql
	 *
	 * @ingroup DB
	**/
	class MyDialect extends Dialect
	{
		const IN_BOOLEAN_MODE = 1;
		
		public function quoteValue($value)
		{
			/// @see Sequenceless for this convention
			
			if ($value instanceof Identifier && !$value->isFinalized())
				return "''"; // instead of 'null', to be compatible with v. 4
			
			return "'" . mysql_real_escape_string($value, $this->getLink()) . "'";
		}
		
		public function quoteField($field)
		{
			if (strpos($field, '.') !== false)
				throw new WrongArgumentException();
			elseif (strpos($field, '::') !== false)
				throw new WrongArgumentException();
			
			return "`{$field}`";
		}
		
		public function quoteTable($table)
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
		
		public function quoteBinary($data)
		{
			return "'".mysql_real_escape_string($data)."'";
		}
		
		public function typeToString(DataType $type)
		{
			if ($type->getId() == DataType::BINARY)
				return 'BLOB';
			
			return parent::typeToString($type);
		}
		
		public function hasTruncate()
		{
			return true;
		}
		
		public function hasMultipleTruncate()
		{
			return false;
		}
		
		public function hasReturning()
		{
			return false;
		}
		
		public function preAutoincrement(DBColumn $column)
		{
			$column->setDefault(null);
			
			return null;
		}
		
		public function postAutoincrement(DBColumn $column)
		{
			return 'AUTO_INCREMENT';
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
