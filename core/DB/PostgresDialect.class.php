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
	 * PostgreSQL dialect.
	 *
	 * @see http://www.postgresql.org/
	 * 
	 * @ingroup DB
	**/
	final class PostgresDialect extends Dialect
	{
		public static $tsConfiguration = 'default_russian';

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
	}
?>