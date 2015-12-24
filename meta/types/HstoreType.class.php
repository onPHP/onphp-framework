<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	 * @see http://www.postgresql.org/docs/8.3/interactive/hstore.html
	**/
	final class HstoreType extends ObjectType
	{
		public function getPrimitiveName()
		{
			return 'hstore';
		}
		
		public function isGeneric()
		{
			return true;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function getDeclaration()
		{
			if ($this->hasDefault())
				return "'{$this->default}'";
		
			return 'null';
		}
		
		public function toColumnType()
		{
			return 'new DataType(DataType::TEXT)';
		}
	}
?>