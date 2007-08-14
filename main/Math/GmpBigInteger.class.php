<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Math
	**/
	final class GmpBigInteger implements BigInteger
	{
		private $resource = null;
		
		public function __construct($number, $base = 10)
		{
			$this->resource = gmp_init($number, $base);
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public static function create($number, $base = 10)
		{
			return new self($number, $base);
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public static function makeFromBinary($binary)
		{
			if ($binary === null || $binary === '')
				throw new WrongArgumentException('can\'t make number from emptyness');
			
			if (ord($binary) > 127)
				throw new WrongArgumentException('only positive numbers allowed');
			
			$number = self::create(0);
			
			$length = strlen($binary);
			for ($i = 0; $i < $length; ++$i) {
				$number = $number->
					mul(self::create(256))->
					add(self::create(ord($binary)));
				$binary = substr($binary, 1);
			}
			
			return $number;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function add(BigInteger $x)
		{
			$this->resource = gmp_add($this->resource, $x->resource);
			return $this;
		}
		
		public function compareTo(BigInteger $x)
		{
			$out = gmp_cmp($this->resource, $x->resource);
			if ($out == 0)
				return 0;
			elseif ($out > 0)
				return 1;
			else
				return -1;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function mod(BigInteger $mod)
		{
			$this->resource = gmp_mod($this->resource, $mod->resource);
			return $this;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function pow(/* integer */ $exp)
		{
			Assert::isInteger($exp);
			$this->resource = gmp_pow($this->resource, $exp);
			return $this;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function modPow(/* integer */ $exp, BigInteger $mod)
		{
			Assert::isInteger($exp);
			$this->resource = gmp_powm(
				$this->resource, 
				$exp, 
				$mod->resource
			);
			return $this;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function subtract(BigInteger $x)
		{
			$this->resource = gmp_sub($this->resource, $x->resource);
			return $this;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function mul(BigInteger $x)
		{
			$this->resource = gmp_mul($this->resource, $x->resource);
			return $this;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function div(BigInteger $x)
		{
			$this->resource = gmp_div($this->resource, $x->resource);
			return $this;
		}
		
		public function toString()
		{
			return gmp_strval($this->resource);
		}
		
		public function toBinary()
		{
			$withZero = gmp_cmp($this->resource, 0);
			
			if ($withZero < 0)
				throw new WrongArgumentException('only positive integers allowed');
			elseif ($withZero === 0)
				return "\x00";
			
			$bytes = array();
			
			$dividend = $this->resource;
			while (gmp_cmp($dividend, 0) > 0) {
				list ($dividend, $reminder) = gmp_div_qr($dividend, 256);
				array_unshift($bytes, gmp_intval($reminder));
			}

			if ($bytes[0] > 127) {
				array_unshift($bytes, 0);
			}

			$binary = null;
			foreach ($bytes as $byte) {
				$binary .= pack('C', $byte);
			}
			
			return $binary;
		}
		
		public function intValue()
		{
			$intValue = gmp_intval($this->resource);
			
			if ((string)$intValue !== gmp_strval($this->resource))
				throw new WrongArgumentException('can\'t represent itself by integer');
				
			return $intValue;
		}
		
		public function floatValue()
		{
			return floatval(gmp_strval($this->resource));
		}
	}
?>