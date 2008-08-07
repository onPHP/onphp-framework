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
/* $Id$ */

	/**
	 * based on pseudorandom generator mt_rand
	 * 
	 * @ingroup Math
	**/
	final class MtRandomSource extends Singleton implements RandomSource
	{
		/**
		 * @return MtRandomSource
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function getBytes($numberOfBytes)
		{
			Assert::isPositiveInteger($numberOfBytes);
			
			$bytes = null;
			for ($i = 0; $i < $numberOfBytes; $i += 4) {
				$bytes .= pack('L', mt_rand());
			}
			
			return substr($bytes, 0, $numberOfBytes);
		}
	}
?>