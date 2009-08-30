<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Nickolay G. Korolyov                       *
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
	class FloatType extends IntegerType
	{
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
	}
?>