<?php
/***************************************************************************
 *   Copyright (C) by Georgiy T. Kutsurua                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 ***************************************************************************/

namespace OnPHP\Core\Base;

use DateTimeZone;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * Timestamp with time zone
 *
 * @see Timestamp, Date
 * @ingroup Base
 */
class TimestampTZ extends Timestamp
{
	/**
	 * @static
	 * @return string
	 */
	protected static function getFormat(): string
	{
		return 'Y-m-d H:i:sO';
	}

	/**
	 * @param null $zone
	 * @return Timestamp|TimestampTZ
	 * @throws WrongArgumentException
	 */
	public function toTimestamp($zone = null): Timestamp
	{
		if (null === $zone) {
			if(
				!($zone instanceof DateTimeZone)
				&& is_scalar($zone)
			) {
				$zone = new DateTimeZone($zone);
			}

			return new static($this->toStamp(), $zone);
		}

		return  parent::toTimestamp();
	}

	/**
	 * @param Date $left
	 * @param Date $right
	 * @return int
	 * @throws WrongArgumentException
	 */
	public static function compare(Date $left, Date $right): int
	{
		Assert::isTrue(
			(
				$left instanceof TimestampTZ
				&& $right instanceof TimestampTZ
			)
		);

		return parent::compare($left, $right);
	}
}