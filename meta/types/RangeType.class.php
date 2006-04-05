<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class RangeType extends ObjectType
	{
		public function isGeneric()
		{
			return true;
		}
		
		public function toColumnType()
		{
			// XXX: hacky
			return 'DataType::create(DataType::INTEGER)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::range';
		}
	}
?>