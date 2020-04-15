<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Form\Primitives;

use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\TimestampRange;

/**
 * @ingroup Primitives
**/
final class PrimitiveTimestampRange extends PrimitiveDateRange
{
	private $className = null;

	/**
	 * @return PrimitiveTimestampRange
	**/
	public static function create($name)
	{
		return new self($name);
	}

	protected function getObjectName()
	{
		return TimestampRange::class;
	}

	protected function makeRange($string)
	{
		if (strpos($string, ' - ') !== false) {
			list($first, $second) = explode(' - ', $string);

			return TimestampRange::create(
				new Timestamp(trim($first)),
				new Timestamp(trim($second))
			);
		}

		throw new WrongArgumentException();
	}
}
?>
