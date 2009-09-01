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

	/**
	 * @ingroup Types
	**/
	final class SmallInteger extends Integer
	{
		protected $min = Integer::SIGNED_SMALL_MIN;
		protected $max = Integer::SIGNED_SMALL_MAX;
		
		/**
		 * @return SmallInteger
		**/
		public static function create($value = null)
		{
			return new self($value);
		}
	}
?>