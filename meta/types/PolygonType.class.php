<?php

/***************************************************************************
 *   Copyright (C) 2012 by Nikita V. Konstantinov                          *
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
	class PolygonType extends ObjectType
	{
		public function getPrimitiveName()
		{
			return 'Polygon';
		}
		
		public function isGeneric()
		{
			return true;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::POLYGON)';
		}
	}
?>