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
	 * Inexistent imaginary helper for OSQL's Query self-identification.
	 * 
	 * @ingroup DB
	 * @ingroup Module
	**/
	final class ImaginaryDialect extends Dialect
	{
		/**
		 * @return ImaginaryDialect
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function preAutoincrement(DBColumn $column)
		{
			return null;
		}
		
		public function postAutoincrement(DBColumn $column)
		{
			return 'AUTOINCREMENT';
		}
		
		public static function quoteValue($value)
		{
			return $value;
		}
		
		public static function quoteField($field)
		{
			return $field;
		}
		
		public static function quoteTable($table)
		{
			return $table;
		}
		
		public function hasTruncate()
		{
			return false;
		}
		
		public function hasMultipleTruncate()
		{
			return false;
		}

		public function fieldToString($field)
		{
			return
				$field instanceof DialectString
					? $field->toDialectString($this)
					: $field;
		}
		
		public function valueToString($value)
		{
			return
				$value instanceof DBValue
					? $value->toDialectString($this)
					: $value;
		}

		public function fullTextSearch($field, $words, $logic)
		{
			return
				'("'
					.$this->fieldToString($field)
					.'" CONTAINS "'
					.implode($logic, $words)
				.'")';
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			return
				'(RANK BY "'.$this->fieldToString($field).'" WHICH CONTAINS "'
					.implode($logic, $words)
				.'")';
		}
	}
?>