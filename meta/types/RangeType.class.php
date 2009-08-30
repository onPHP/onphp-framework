<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
	class RangeType extends InternalType
	{
		public function toPrimitive()
		{
			return 'Primitive::range';
		}
		
		public function toColumnType()
		{
			return null;
		}
		
		public function getSuffixList()
		{
			return array('min', 'max');
		}
	}
?>