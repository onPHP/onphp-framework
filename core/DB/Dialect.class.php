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
	 * Base (aka ANSI) SQL dialect.
	 *
	 * @ingroup DB
	 * @ingroup Module
	**/
	abstract class /* ANSI's */ Dialect
		extends Singleton
		implements Instantiatable
	{
		abstract public function preAutoincrement(DBColumn $column);
		abstract public function postAutoincrement(DBColumn $column);
		
		abstract public function hasTruncate();
		abstract public function hasMultipleTruncate();
		abstract public function hasReturning();
		
		/**
			must be implemented too:
			
			public static function quoteValue($value);
		**/
		
		public static function quoteField($field)
		{
			return self::quoteTable($field);
		}
		
		public static function quoteTable($table)
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
		
		public function quoteBinary($data)
		{
			return $this->quoteValue($data);
		}
		
		public function unquoteBinary($data)
		{
			return $data;
		}
		
		public function typeToString(DataType $type)
		{
			return $type->getName();
		}
		
		public function toFieldString($expression)
		{
			return $this->toNeededString($expression, 'quoteField');
		}
		
		public function toValueString($expression)
		{
			return $this->toNeededString($expression, 'quoteValue');
		}
		
		private function toNeededString($expression, $method)
		{
			$string = null;
			
			if (null !== $expression) {
				if ($expression instanceof DialectString) {
					if ($expression instanceof Query)
						$string .= '('.$expression->toDialectString($this).')';
					else
						$string .= $expression->toDialectString($this);
				} else {
					$string .= $this->$method($expression);
				}
			}
			
			return $string;
		}
		
		public function fieldToString($field)
		{
			return
				$field instanceof DialectString
					? $field->toDialectString($this)
					: $this->quoteField($field);
		}
		
		public function valueToString($value)
		{
			return
				$value instanceof DBValue
					? $value->toDialectString($this)
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