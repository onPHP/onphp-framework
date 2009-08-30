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
	 * PostgreSQL dialect.
	 * 
	 * @see http://www.postgresql.org/
	 * 
	 * @ingroup DB
	**/
	final class PostgresDialect extends Dialect
	{
		public static $tsConfiguration = 'default_russian';
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public static function getTsConfiguration()
		{
			return self::$tsConfiguration;
		}

		public static function setTsConfiguration($configuration)
		{
			self::$tsConfiguration = $configuration;
		}
		
		public static function quoteValue(&$value)
		{
			return "'".pg_escape_string($value)."'";
		}

		public static function toCasted($field, $type)
		{
			return "{$field}::{$type}";
		}

		public static function prepareFullText($words, $logic)
		{
			Assert::isArray($words);
			
			$glue = ($logic == DB::FULL_TEXT_AND) ? ' & ' : ' | ';

			return
				strtolower(
					implode(
						$glue,
						array_map(
							array('PostgresDialect', 'quoteValue'),
							$words
						)
					)
				);
		}
		
		public function fullTextSearch($field, $words, $logic)
		{
			$searchString = self::prepareFullText($words, $logic);
			$field = $this->fieldToString($field);

			return
				"({$field} @@ to_tsquery('".self::$tsConfiguration."', ".
				self::quoteValue($searchString)."))";
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			$searchString = self::prepareFullText($words, $logic);
			$field = $this->fieldToString($field);
			
			return
				"rank({$field}, to_tsquery('".self::$tsConfiguration."', ".
				self::quoteValue($searchString)."))";
		}
		
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			Assert::isTrue(
				(($table = $column->getTable()) !== null)
				// strict checking commented out due to a bug,
				// fixed only in >0.5
				// && ($column->getDefault() === null)
			);
			
			$sequenceName = $table->getName().'_id';
			
			$prepend = 'CREATE SEQUENCE "'.$sequenceName.'";';
			
			$column->setDefault(
				new SQLFunction('nextval', new DBValue($sequenceName))
			);
			
			return null;
		}
	}
?>