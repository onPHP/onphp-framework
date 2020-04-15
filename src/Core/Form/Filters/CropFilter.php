<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Form\Filters;

use OnPHP\Core\Base\Assert;

/**
 * @see RegulatedPrimitive::addImportFilter()
 * 
 * @ingroup Filters
**/
final class CropFilter implements Filtrator
{
	private $start	= 0;
	private $length	= 0;

	/**
	 * @return CropFilter
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return CropFilter
	**/
	public function setStart($start)
	{
		Assert::isPositiveInteger($start);

		$this->start = $start;

		return $this;
	}

	/**
	 * @return CropFilter
	**/
	public function setLength($length)
	{
		Assert::isPositiveInteger($length);

		$this->length = $length;

		return $this;
	}

	public function apply($value)
	{
		return
			$this->length
				? mb_strcut($value, $this->start, $this->length)
				: mb_strcut($value, $this->start);
	}
}
?>