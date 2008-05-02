<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	class Integer extends Numeric
	{
		const SIGNED_SMALL_MIN = -32768;
		const SIGNED_SMALL_MAX = +32767;
		
		const SIGNED_MIN = -2147483648;
		const SIGNED_MAX = +2147483647;
		
		const SIGNED_BIG_MIN = -9223372036854775808;
		const SIGNED_BIG_MAX = 9223372036854775807;
		
		const UNSIGNED_SMALL_MAX = 65535;
		const UNSIGNED_MAX = 4294967295;
		
		protected $min = self::SIGNED_MIN;
		protected $max = self::SIGNED_MAX;
		
		protected function checkValue($value)
		{
			return Assert::checkInteger($value);
		}
		
		protected function castValue($value)
		{
			return (int) $value;
		}
	}
?>