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

	class GmpBigInteger implements BigInteger 
	{
		private $resource = null;
		
		public function __construct($number, $base = 10)
		{
			$this->resource = gmp_init($number, $base);
		}
		
		public static function create($number, $base = 10)
		{
			return new self($number, $base);
		}
		
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
		
		public function mod(BigInteger $mod)
		{
			$this->resource = gmp_mod($this->resource, $mod->resource);
			return $this;
		}
		
		public function pow(/* integer */ $exp)
		{
			Assert::isInteger($exp);
			$this->resource = gmp_pow($this->resource, $exp);
			return $this;
		}
		
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
		
		public function subtract(BigInteger $x)
		{
			$this->resource = gmp_sub($this->resource, $x->resource);
			return $this;
		}
		
		public function mul(BigInteger $x)
		{
			$this->resource = gmp_mul($this->resource, $x->resource);
			return $this;
		}
		
		public function div(BigInteger $x)
		{
			$this->resource = gmp_div($this->resource, $x->resource);
			return $this;
		}
		
		public function toString()
		{
			return gmp_strval($this->resource);
		}
	}
?>