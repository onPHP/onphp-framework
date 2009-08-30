<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Atom for ternary-based logic.
	 * 
	 * @ingroup Base
	**/
	final class Ternary implements Stringable
	{
		private $trinity = null;	// ;-)
		
		public function __construct($boolean = null)
		{
			return $this->setValue($boolean);
		}
		
		/**
		 * @return Ternary
		**/
		public static function create($boolean = null)
		{
			return new self($boolean);
		}

		/**
		 * @return Ternary
		**/
		public static function spawn($value, $true, $false, $null = null)
		{
			if ($value === $true)
				return new Ternary(true);
			elseif ($value === $false)
				return new Ternary(false);
			elseif (($value === $null) || ($null === null))
				return new Ternary(null);
			else /* if ($value !== $null && $null !== null) or anything else */
				throw new WrongArgumentException(
					"failed to spawn Ternary from '{$value}' switching on ".
					"'{$true}', '{$false}' and '{$null}'"
				);
		}
		
		public function isNull()
		{
			return (null === $this->trinity);
		}
		
		public function isTrue()
		{
			return (true === $this->trinity);
		}
		
		public function isFalse()
		{
			return (false === $this->trinity);
		}
		
		/**
		 * @return Ternary
		**/
		public function setNull()
		{
			$this->trinity = null;
			
			return $this;
		}
		
		/**
		 * @return Ternary
		**/
		public function setTrue()
		{
			$this->trinity = true;
			
			return $this;
		}
		
		/**
		 * @return Ternary
		**/
		public function setFalse()
		{
			$this->trinity = false;
			
			return $this;
		}

		public function getValue()
		{
			return $this->trinity;
		}
		
		/**
		 * @return Ternary
		**/
		public function setValue($boolean = null)
		{
			Assert::isTernaryBase($boolean);

			$this->trinity = $boolean;

			return $this;
		}
		
		public function decide($true, $false, $null = null)
		{
			if ($this->trinity === true)
				return $true;
			elseif ($this->trinity === false)
				return $false;
			elseif ($this->trinity === null)
				return $null;

			throw new WrongStateException(
				'mama, weer all crazee now!' // (c) Slade
			);
		}
		
		public function toString()
		{
			return $this->decide('true', 'false', 'null');
		}
	}
?>