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
	 * @ingroup Math
	**/
	interface ExternalBigInteger extends Stringable
	{
		/**
		 * @return BigNumberFactory
		**/
		public static function getFactory();
		
		/**
		 * @return ExternalBigInteger
		**/
		public function add(ExternalBigInteger $x);
		
		public function compareTo(ExternalBigInteger $x);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function mod(ExternalBigInteger $mod);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function pow(ExternalBigInteger $exp);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function modPow(ExternalBigInteger $exp, ExternalBigInteger $mod);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function subtract(ExternalBigInteger $x);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function mul(ExternalBigInteger $x);
		
		/**
		 * @return ExternalBigInteger
		**/
		public function div(ExternalBigInteger $x);
		
		/**
		 * convert to big-endian signed two's complement notation
		**/
		public function toBinary();
		
		public function intValue();
		public function floatValue();
	}
?>