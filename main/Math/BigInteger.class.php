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

	interface BigInteger
	{
		public function add(BigInteger $x);
		public function compareTo(BigInteger $x);
		public function mod(BigInteger $mod);
		public function pow(/* integer */ $exp);
		public function modPow(/* integer */ $exp, BigInteger $mod);
		public function subtract(BigInteger $x);
		public function mul(BigInteger $x);
		public function div(BigInteger $x);
		public function toString();
		
		/**
		 * convert to big-endian signed two's complement notation
		**/
		public function toBinary();
	}
?>