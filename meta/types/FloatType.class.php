<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	class FloatType extends BasePropertyType
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
			return 'DataType::create(DataType::DOUBLE)';
		}

		public function toPrimitive()
		{
			return 'Primitive::float';
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov, Nickolay Korolyov       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	class FloatType extends BasePropertyType
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
			return 'DataType::create(DataType::DOUBLE)';
		}

		public function toPrimitive()
		{
			return 'Primitive::float';
		}
	}
?>