<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Anton E. Lebedevich                        *
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
		
		public function __construct(
			ExternalBigInteger $gen,
			ExternalBigInteger $modulus
		)
		{
			Assert::brothers($gen, $modulus);
			
			$this->gen = $gen;
			$this->modulus = $modulus;
		}
		
		/**
		 * @return DiffieHellmanParameters
		**/
		public static function create(
			ExternalBigInteger $gen,
			ExternalBigInteger $modulus
		)
		{
			return new self($gen, $modulus);
		}
		
		/**
		 * @return ExternalBigInteger
		**/
		public function getGen()
		{
			return $this->gen;
		}
		
		/**
		 * @return ExternalBigInteger
		**/
		public function getModulus()
		{
			return $this->modulus;
		}
	}
?>