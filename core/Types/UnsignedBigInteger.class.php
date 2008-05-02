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
	final class UnsignedBigInteger extends Integer
	{
		protected $min = 0;
		protected $max = null;
		
		/**
		 * @return UnsignedBigInteger
		**/
		public static function create($value = null)
		{
			return new self($value);
		}
	}
?>