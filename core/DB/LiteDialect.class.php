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
	 * SQLite dialect.
	 *
	 * @see http://www.sqlite.org/
	 *
	 * @ingroup DB
	**/
	class LiteDialect extends Dialect implements Instantiatable
	{
		public function quoteValue($value)
		{
			/// @see Sequenceless for this convention
			
			if ($value instanceof Identifier && !$value->isFinalized())
				return 'null';
			
			if (Assert::checkInteger($value))
				return $value;
			
			return "'" .sqlite_escape_string($value)."'";
		}
		
		public static function dropTableMode($cascade = false)
		{
			return null;
		}
		
		public function quoteBinary($data)
		{
			return "'" .sqlite_udf_encode_binary($data)."'";
		}
		
		public function unquoteBinary($data)
		{
			return sqlite_udf_decode_binary($data);
		}
		
		public function typeToString(DataType $type)
		{
			switch ($type->getId()) {
				case DataType::BIGINT:
					
					return 'INTEGER';
				
				case DataType::BINARY:
					
					return 'BLOB';
			}
			
			return parent::typeToString($type);
		}
		
		public function logicToString($logic)
		{
			switch ($logic) {
				case PostfixUnaryExpression::IS_FALSE:
					return '= '.$this->quoteValue('0');
				case PostfixUnaryExpression::IS_TRUE:
					return '= '.$this->quoteValue('1');
			}
			return parent::logicToString($logic);
		}
		
		public function literalToString($literal)
		{
			switch ($literal) {
				case self::LITERAL_FALSE:
					return $this->quoteValue('0');
				case self::LITERAL_TRUE:
					return $this->quoteValue('1');
			}
			return parent::literalToString($literal);
		}
		
		public function preAutoincrement(DBColumn $column)
		{
			self::checkColumn($column);
			
			return null;
		}
		
		public function postAutoincrement(DBColumn $column)
		{
			self::checkColumn($column);
			
			return null; // or even 'AUTOINCREMENT'?
		}
		
		public function hasTruncate()
		{
			return false;
		}
		
		public function hasMultipleTruncate()
		{
			return false;
		}
		
		public function hasReturning()
		{
			return false;
		}
		
		private static function checkColumn(DBColumn $column)
		{
			$type = $column->getType();
			
			Assert::isTrue(
				(
					$type->getId() == DataType::BIGINT
					|| $type->getId() == DataType::INTEGER
				)
				&& $column->isPrimaryKey()
			);
		}
	}
