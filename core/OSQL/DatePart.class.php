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

	/**
	 * @see http://www.postgresql.org/docs/current/interactive/functions-datetime.html#FUNCTIONS-DATETIME-EXTRACT
	 * 
	 * @ingroup OSQL
	**/
	final class DatePart extends Enumeration
	{
		const CENTURY			= 1;
		const DAY				= 2;
		const DECADE			= 3;
		const DOW				= 4; // day of week
		const DOY				= 5; // day of year
		const EPOCH				= 6;
		const HOUR				= 7;
		const MICROSECONDS		= 8;
		const MILLENIUM			= 9; // damn useful
		const MILLISECONDS		= 10;
		const MINUTE			= 11;
		const MONTH				= 12;
		const QUARTER			= 13;
		const SECOND			= 14;
		const TIMEZONE			= 15;
		const TIMEZONE_HOUR		= 16;
		const TIMEZONE_MINUTE	= 17;
		const WEEK				= 18;
		const YEAR				= 19;
		
		protected $names = array(
			self::CENTURY			=> 'CENTURY',
			self::DAY				=> 'DAY',
			self::DECADE			=> 'DECADE',
			self::DOW				=> 'DOW',
			self::DOY				=> 'DOY',
			self::EPOCH				=> 'EPOCH',
			self::HOUR				=> 'HOUR',
			self::MICROSECONDS		=> 'MICROSECONDS',
			self::MILLENIUM			=> 'MILLENIUM',
			self::MILLISECONDS		=> 'MILLISECONDS',
			self::MINUTE			=> 'MINUTE',
			self::MONTH				=> 'MONTH',
			self::QUARTER			=> 'QUARTER',
			self::SECOND			=> 'SECOND',
			self::TIMEZONE			=> 'TIMEZONE',
			self::TIMEZONE_HOUR		=> 'TIMEZONE_HOUR',
			self::TIMEZONE_MINUTE	=> 'TIMEZONE_MINUTE',
			self::WEEK				=> 'WEEK',
			self::YEAR				=> 'YEAR'
		);
	}
