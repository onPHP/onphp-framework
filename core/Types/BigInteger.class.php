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
	final class BigInteger extends Integer
	{
		protected $min = Integer::SIGNED_BIG_MIN;
		protected $max = Integer::SIGNED_BIG_MAX;
		
		/**
		 * @return BigInteger
		**/
		public static function create($value = null)
		{
			return new self;
		}
	}
?>