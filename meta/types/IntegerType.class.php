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
/* $Id$ */

	/**
	 * @ingroup MetaTypes
	**/
	class IntegerType extends BasePropertyType
	{
		public function getSize()
		{
			return 4;
		}
		
		public function getPrimitiveName()
		{
			return 'integer';
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return IntegerType
		**/
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
	}
?>