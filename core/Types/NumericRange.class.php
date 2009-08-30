<?php
/***************************************************************************
 *   Copyright (C) 2009 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Numeric interval implementation.
	 * 
	 * @ingroup Types
	**/
	final class NumericRange extends Range
	{
		/**
		 * @return NumericRange
		**/
		public static function create($start = null, $end = null)
		{
			return new self($start, $end);
		}
		
		/**
		 * @return NumericRange
		**/
		public static function lazyCreate($start = null, $end = null)
		{
			if ($start > $end)
				self::swap($start, $end);
			
			return new self($start, $end);
		}
		
		/* void */ protected function checkNumber($number)
		{
			Assert::isNumeric($number);
		}
	}
?>