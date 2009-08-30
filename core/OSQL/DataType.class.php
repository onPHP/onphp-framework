<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Generic SQL data types.
	 * 
	 * @ingroup OSQL
	**/
	final class DataType
		extends IdentifiableObject
		/* mimics NamedObject and Enumeration */
		implements DialectString
	{
		const SMALLINT			= 0x000001;
		const INTEGER			= 0x000002;
		const BIGINT			= 0x000003;
		const NUMERIC			= 0x011004;
		
		const REAL				= 0x000005;
		const DOUBLE			= 0x000006;
		
		const BOOLEAN			= 0x000007;
		
		const CHAR				= 0x000108;
		const VARCHAR			= 0x000109;
		const TEXT				= 0x00000A;
		
		const DATE				= 0x00000B;
		const TIME				= 0x10100C;
		const TIMESTAMP			= 0x10100D;
		
		const HAVE_SIZE			= 0x000100;
		const HAVE_PRECISION	= 0x001000;
		const HAVE_SCALE		= 0x010000;
		const HAVE_TIMEZONE		= 0x100000;
		
		private $name	= null;
		
		private $size		= null;
		private $precision	= null;
		private $scale		= null;
		
		private $null		= true;
		private $timezone	= false;
		
		protected $names = array(
			self::SMALLINT		=> 'SMALLINT',
			self::INTEGER		=> 'INTEGER',
			self::BIGINT		=> 'BIGINT',
			self::NUMERIC		=> 'NUMERIC',
			
			self::REAL			=> 'REAL',
			self::DOUBLE		=> 'DOUBLE PRECISION',
			
			self::BOOLEAN		=> 'BOOLEAN',
			
			self::CHAR			=> 'CHARACTER',
			self::VARCHAR		=> 'CHARACTER VARYING',
			self::TEXT			=> 'TEXT',
			
			self::DATE			=> 'DATE',
			self::TIME			=> 'TIME',
			self::TIMESTAMP		=> 'TIMESTAMP'
		);
		
		public static function create($id)
		{
			return new DataType($id);
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public static function getAnyId()
		{
			return self::BOOLEAN;
		}
		
		public function getSize()
		{
			return $this->size;
		}
		
		public function setSize($size)
		{
			Assert::isInteger($size);
			Assert::isTrue(($this->id & self::HAVE_SIZE) > 0);
			
			$this->size = $size;
			
			return $this;
		}
		
		public function getPrecision()
		{
			return $this->precision;
		}
		
		public function setPrecision($precision)
		{
			Assert::isInteger($precision);
			Assert::isTrue(($this->id & self::HAVE_PRECISION) > 0);
			
			$this->precision = $precision;
			
			return $this;
		}
		
		public function getScale()
		{
			return $this->scale;
		}
		
		public function setScale($scale)
		{
			Assert::isInteger($scale);
			Assert::isTrue(($this->id & self::HAVE_SCALE) > 0);
			
			$this->scale = $scale;
			
			return $this;
		}
		
		public function setTimezoned($zoned = false)
		{
			Assert::isTrue(($this->id & self::HAVE_TIMEZONE) > 0);
			
			$this->timezone = true === $zoned;
			
			return $this;
		}
		
		public function isTimezoned()
		{
			return $this->timezone;
		}
		
		public function setNull($isNull = false)
		{
			$this->null = $isNull === true;
			
			return $this;
		}
		
		public function isNull()
		{
			return $this->null;
		}
		
		public function typeToString(Dialect $dialect)
		{
			if (
				$this->id == self::BIGINT
				&& $dialect instanceof LiteDialect
			) {
				return $this->names[self::INTEGER];
			}
			
			return $this->name;
		}
		
		public function toString(Dialect $dialect)
		{
			$out = $this->typeToString($dialect);
			
			if ($this->id & self::HAVE_SIZE) {
				
				if (!$this->size)
					throw new WrongStateException(
						"type '{$this->name}' should have size set"
					);
				
				$out .= "({$this->size})";
			}
			
			if ($this->id & self::HAVE_PRECISION) {
				
				if ($this->precision) {
					
					switch ($this->id) {
						
						case self::TIME:
						case self::TIMESTAMP:
							
							$out .= "({$this->precision})";
							break;
						
						case self::NUMERIC:
							
							$out .=
								$this->scale
									? "({$this->precision}, {$this->scale})"
									: "({$this->precision})";
							break;
						
						default:
							
							throw new WrongStateException();
					}
					
				}
			}
			
			if ($this->id & self::HAVE_TIMEZONE)
				$out .= $dialect->timeZone($this->timezone);
			
			$out .=
				$this->null
					? " NULL"
					: " NOT NULL";
			
			return $out;
		}
	}
?>