<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutusurua                            *
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
	class UuidType extends StringType
	{

		public function getPrimitiveName()
		{
			return 'uuid';
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return UuidType
		**/
		public function setDefault($default)
		{
			Assert::isUuid(
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
			return 'DataType::create(DataType::UUID)';
		}
	}
?>