<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	class SmallIntegerType extends IntegerType
	{
		public function toColumnType()
		{
			return 'DataType::create(DataType::SMALLINT)';
		}
		
		public function toPrimitiveLimits()
		{
			return 'setMin(-32768)->'."\n".'setMax(32767)';
		}
	}
?>