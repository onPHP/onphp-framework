<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Nickolay G. Korolyov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	class FloatType extends IntegerType
	{
		protected $precision = 0;
		
		/**
		 * @return FloatType
		**/
		public function setPrecision($precision)
		{
			$this->precision = $precision;
			
			return $this;
		}
		
		public function getPrecision()
		{
			return $this->precision;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return FloatType
		**/
		public function setDefault($default)
		{
			Assert::isFloat(
				$default,
				"strange default value given - '{$default}'"
			);

			$this->default = $default;

			return $this;
		}

		public function toColumnType()
		{
			return 'DataType::create(DataType::REAL)';
		}

		public function toPrimitive()
		{
			return 'Primitive::float';
		}
		
		public function toXsdType()
		{
			return 'xsd:float';
		}
	}
?>