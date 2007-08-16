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

	final class GmpBigInteger implements BigInteger
	{
		private $resource = null;
		
		/**
		 * @return GmpBigInteger
		**/
		public static function make($number, $base = 10)
		{
			$result = new self;
			$result->resource = gmp_init($number, $base);
			return $result;
		}
		
		public static function getFactory()
		{
			return GmpBigIntegerFactory::me();
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
			
			$number = self::make(0);
			
			$length = strlen($binary);
			for ($i = 0; $i < $length; ++$i) {
				$number = $number->
					mul(self::make(256))->
					add(self::make(ord($binary)));
				$binary = substr($binary, 1);
			}
			
			return $number;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function add(BigInteger $x)
		{
			$result = new self();
			$result->resource = gmp_add($this->resource, $x->resource);
			return $result;
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
			$result = new self();
			$result->resource = gmp_mod($this->resource, $mod->resource);
			return $result;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function pow(BigInteger $exp)
		{
			$result = new self();
			$result->resource = gmp_pow($this->resource, $exp->intValue());
			return $result;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function modPow(BigInteger $exp, BigInteger $mod)
		{
			$result = new self();
			$result->resource = gmp_powm(
				$this->resource, 
				$exp->resource, 
				$mod->resource
			);
			return $result;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function subtract(BigInteger $x)
		{
			$result = new self();
			$result->resource = gmp_sub($this->resource, $x->resource);
			return $result;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function mul(BigInteger $x)
		{
			$result = new self();
			$result->resource = gmp_mul($this->resource, $x->resource);
			return $result;
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function div(BigInteger $x)
		{
			$result = new self();
			$result->resource = gmp_div($this->resource, $x->resource);
			return $result;
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
			// TODO: throw WrongArgumetException like intValue
			return floatval(gmp_strval($this->resource));
		}
	}
?>