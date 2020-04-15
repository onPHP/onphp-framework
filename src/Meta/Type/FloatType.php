<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Nickolay G. Korolyov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Type;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\OSQL\DataType;

/**
 * @ingroup Types
**/
class FloatType extends IntegerType
{
	protected $precision = 0;
	
	public function getPrimitiveName()
	{
		return 'float';
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

	/**
	 * @return NumericType
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
	
	public function toColumnType()
	{
		return DataType::class.'::create('.DataType::class.'::REAL)';
	}
}
?>