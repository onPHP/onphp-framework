<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see http://tools.ietf.org/html/rfc2631
	 * 
	 * @ingroup Crypto
	**/
	final class DiffieHellmanParameters
	{
		private $gen		= null;
		private $modulus	= null;
		
		public function __construct(BigInteger $gen, BigInteger $modulus)
		{
			Assert::brothers($gen, $modulus);
			
			$this->gen = $gen;
			$this->modulus = $modulus;
		}
		
		/**
		 * @return DiffieHellmanParameters
		**/
		public static function create(BigInteger $gen, BigInteger $modulus)
		{
			return new self($gen, $modulus);
		}
		
		/**
		 * @return BigInteger
		**/
		public function getGen()
		{
			return $this->gen;
		}
		
		/**
		 * @return BigInteger
		**/
		public function getModulus()
		{
			return $this->modulus;
		}
	}
