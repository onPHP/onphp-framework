<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
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
		private static $tsConfiguration = 'utf8_russian';
		private static $rankFunction = 'rank';

		/**
		 * @return PostgresDialect
		**/
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

		public static function setRankFunction($rank)
		{
			self::$rankFunction = $rank;
		}

		public static function quoteValue($value)
		{
			return "'".pg_escape_string($value)."'";
		}

		public static function toCasted($field, $type)
		{
			return "{$field}::{$type}";
		}

		public static function prepareFullText(array $words, $logic)
		{
			$glue = ($logic == DB::FULL_TEXT_AND) ? ' & ' : ' | ';

			return
				mb_strtolower(
					implode(
						$glue,
						array_map(
							array('PostgresDialect', 'quoteValue'),
							$words
						)
					)
				);
		}

		public function quoteBinary($data)
		{
			return "'".pg_escape_bytea($data)."'"; // deleted first E in qoutes; it was - "E'..."
		}

		public function unquoteBinary($data)
		{
			return pg_unescape_bytea($data);
		}

		public function typeToString(DataType $type)
		{
			if ($type->getId() == DataType::BINARY)
				return 'BYTEA';

			if (defined('POSTGRES_IP4_ENABLED')) {

				if ($type->getId() == DataType::IP)
					return 'ip4';

				if ($type->getId() == DataType::IP_RANGE)
					return 'ip4r';
			}

			if ($type->getId() == DataType::UUID)
				return 'UUID';

			return parent::typeToString($type);
		}

		public function hasTruncate()
		{
			return true;
		}

		public function hasMultipleTruncate()
		{
			return true;
		}

		public function hasReturning()
		{
			return true;
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
				self::$rankFunction."({$field}, to_tsquery('".self::$tsConfiguration."', ".
				self::quoteValue($searchString)."))";
		}

		public function preAutoincrement(DBColumn $column)
		{
			self::checkColumn($column);

			return
				'CREATE SEQUENCE "'
				.$this->makeSequenceName($column).'";';
		}

		public function postAutoincrement(DBColumn $column)
		{
			self::checkColumn($column);

			return
				'default nextval(\''
				.$this->makeSequenceName($column).'\')';
		}

		public function quoteIpInRange($range, $ip)
		{
			$string = '';

			if ($ip instanceof DialectString)
				$string .= $ip->toDialectString($this);
			else
				$string .= $this->quoteValue($ip);

			$string .= ' <<= ';

			if ($range instanceof DialectString)
				$string .= $range->toDialectString($this);
			else
				$string .= $this->quoteValue($range);

			return $string;
		}

		public function quoteArray($values, $type)
		{
			// add qoutes
			foreach($values as &$item) {
				if( $type===DataType::INTEGER ) {
					$item = intval($item);
				} elseif( $type===DataType::VARCHAR ) {
					$item = $this->quoteValue($item);
				} else {
					throw new WrongArgumentException('unknown type of array!');
				}
			}
			return 'ARRAY['.implode(', ',$values).']';
		}

		protected function makeSequenceName(DBColumn $column)
		{
			return $column->getTable()->getName().'_'.$column->getName();
		}

		private static function checkColumn(DBColumn $column)
		{
			Assert::isTrue(
				($column->getTable() !== null)
				&& ($column->getDefault() === null)
			);
		}
	}
?>