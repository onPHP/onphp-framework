<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	**/
	class IntegerType extends BasePropertyType
	{
		public function setDefault($default)
		{
			Assert::isInteger(
				$default,
				"strange default value given - '{$default}'"
			);

			$this->default = $default;
			
			return $this;
		}
		
		public function getDeclaration()
		{
			if ($this->hasDefault())
				return $this->default;
			
			return 'null';
		}
		
		public function isMeasurable()
		{
			return false;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::INTEGER)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::integer';
		}
	}
?>