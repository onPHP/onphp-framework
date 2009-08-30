<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
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
	 * @ingroup Types
	 * @ingroup Module
	**/
	final class Ternary extends BaseType implements Stringable
	{
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
			return (null === $this->value);
		}
		
		public function isTrue()
		{
			return (true === $this->value);
		}
		
		public function isFalse()
		{
			return (false === $this->value);
		}
		
		/**
		 * @return Ternary
		**/
		public function setNull()
		{
			$this->value = null;
			
			return $this;
		}
		
		/**
		 * @return Ternary
		**/
		public function setTrue()
		{
			$this->value = true;
			
			return $this;
		}
		
		/**
		 * @return Ternary
		**/
		public function setFalse()
		{
			$this->value = false;
			
			return $this;
		}
		
		/**
		 * @return Ternary
		**/
		public function setValue($boolean)
		{
			Assert::isTernaryBase($boolean);
			
			$this->value = $boolean;
			
			return $this;
		}
		
		public function decide($true, $false, $null = null)
		{
			if ($this->value === true)
				return $true;
			elseif ($this->value === false)
				return $false;
			elseif ($this->value === null)
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