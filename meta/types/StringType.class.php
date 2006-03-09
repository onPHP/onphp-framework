<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class StringType extends BasePropertyType
	{
		public function getDeclaration()
		{
			return 'null';
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::VARCHAR)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::string';
		}
	}
?>