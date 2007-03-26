<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	final class TimestampRangeType extends DateRangeType
	{
		public function toColumnType()
		{
			// will be called twice with suffixes _start and _end
			return 'DataType::create(DataType::TIMESTAMP)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::timestampRange';
		}
	}
?>