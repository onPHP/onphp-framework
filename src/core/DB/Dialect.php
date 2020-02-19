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
	abstract class /* ANSI's */ Dialect {
		const LITERAL_NULL = 'NULL';
		const LITERAL_TRUE = 'TRUE';
		const LITERAL_FALSE = 'FALSE';
		
		/**
		 * @var DB
		 */
		protected $db = null;
		
		abstract public function preAutoincrement(DBColumn $column);
		abstract public function postAutoincrement(DBColumn $column);
		
		abstract public function hasTruncate();
		abstract public function hasMultipleTruncate();
		abstract public function hasReturning();
		
		abstract public function quoteValue($value);
		
		/**
		 * @deprecated remove after onPHP 1.2+
		 * @return LiteDialect
		**/
		public static function me()
		{
			throw new UnimplementedFeatureException('Deprecated: dialects not extends Singleton now');
		}
		
		public function quoteField($field)
		{
			return $this->quoteTable($field);
		}
		
		public function quoteTable($table)
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
		
		/**
		 * @param DB $db
		 * @return Dialect
		 */
		public function setDB(DB $db)
		{
			$this->db = $db;
			return $this;
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
			if ($type->getId() == DataType::IP)
				return 'varchar(19)';
			
			if ($type->getId() == DataType::IP_RANGE)
				return 'varchar(41)';
			
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
			if (null === $expression)
				throw new WrongArgumentException(
					'not null expression expected'
				);
			
			$string = null;
			
			if ($expression instanceof DialectString) {
				if ($expression instanceof Query)
					$string .= '('.$expression->toDialectString($this).')';
				else
					$string .= $expression->toDialectString($this);
			} else {
				$string .= $this->$method($expression);
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
		
		public function logicToString($logic)
		{
			return $logic;
		}
		
		public function literalToString($literal)
		{
			return $literal;
		}
		
		public function fullTextSearch($field, $words, $logic)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function quoteIpInRange($range, $ip)
		{
			throw new UnimplementedFeatureException();
		}
		
		protected function getLink()
		{
			if (!$this->db)
				throw new WrongStateException('Expected setted db');
			if (!$this->db->isConnected()) {
				$this->db->connect();
			}
			
			return $this->db->getLink();
		}
	}
?>