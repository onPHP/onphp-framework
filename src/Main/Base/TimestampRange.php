<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Base;

/**
 * @see Timestamp
 * @see DateRange
 * 
 * @ingroup Helpers
**/
class TimestampRange extends DateRange
{
	public function getStartStamp(): ?int // null if start is null
	{
		if ($start = $this->getStart()) {
			return $start->toStamp();
		}

		return null;
	}

	public function getEndStamp(): ?int // null if end is null
	{
		if ($end = $this->getEnd()) {
			return $end->toStamp();
		}

		return null;
	}

	/**
	 * @return static
	 */
	public function toTimestampRange(): TimestampRange
	{
		return $this;
	}

	/**
	 * @return string
	 */
	protected function getObjectName(): string
	{
		return Timestamp::class;
	}
}