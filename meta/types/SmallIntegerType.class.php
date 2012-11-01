<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
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
	namespace Onphp;

	class SmallIntegerType extends IntegerType
	{
		public function getSize()
		{
			return 2;
		}
		
		public function toColumnType()
		{
			return '\Onphp\DataType::create(\Onphp\DataType::SMALLINT)';
		}
	}
?>