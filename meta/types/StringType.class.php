<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	class StringType extends BasePropertyType
	{
		/**
		 * @throws WrongArgumentException
		 * @return StringType
		**/
		public function setDefault($default)
		{
			Assert::isString(
				$default,
				"strange default value given - '{$default}'"
			);

			$this->default = $default;
			
			return $this;
		}
		
		public function getDeclaration()
		{
			if ($this->hasDefault())
				return "'{$this->default}'";
			
			return 'null';
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function toColumnType($length = null)
		{
			return
				$length
					? 'DataType::create(DataType::VARCHAR)'
					: 'DataType::create(DataType::TEXT)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::string';
		}
		
		public function toXsdType()
		{
			return 'xsd:string';
		}
	}
?>