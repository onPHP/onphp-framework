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
	 * @ingroup Math
	**/
	namespace Onphp;

	final class GmpBigIntegerFactory extends BigNumberFactory
	{
		/**
		 * @return \Onphp\GmpBigIntegerFactory
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return \Onphp\GmpBigInteger
		**/
		public function makeNumber($number, $base = 10)
		{
			return GmpBigInteger::make($number, $base);
		}
		
		/**
		 * @return \Onphp\GmpBigInteger
		**/
		public function makeFromBinary($binary)
		{
			return GmpBigInteger::makeFromBinary($binary);
		}
		
		/**
		 * @return \Onphp\GmpBigInteger
		**/
		public function makeRandom($stop, RandomSource $source)
		{
			if (is_string($stop)) {
				$stop = $this->makeNumber($stop);
			} elseif (
				$stop instanceof BigInteger
				&& !$stop instanceof GmpBigInteger
			) {
				$stop = $this->makeNumber($stop->toString());
			}
			
			Assert::isTrue($stop instanceof GmpBigInteger);
			
			$numBytes = ceil(log($stop->floatValue(), 2) / 8);
			
			return $this->
				makeFromBinary("\x00".$source->getBytes($numBytes))->
				mod($stop);
		}
	}
?>