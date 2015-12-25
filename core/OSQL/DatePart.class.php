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
class DatePart extends Enumeration
{
    const
        CENTURY = 1,
        DAY = 2,
        DECADE = 3,
        DOW = 4,// day of week
        DOY = 5,// day of year
        EPOCH = 6,
        HOUR = 7,
        MICROSECONDS = 8,
        MILLENIUM = 9, // damn useful
        MILLISECONDS = 10,
        MINUTE = 11,
        MONTH = 12,
        QUARTER = 13,
        SECOND = 14,
        TIMEZONE = 15,
        TIMEZONE_HOUR = 16,
        TIMEZONE_MINUTE = 17,
        WEEK = 18,
        YEAR = 19;

    /** @var array */
    protected $names = [
        self::CENTURY => 'CENTURY',
        self::DAY => 'DAY',
        self::DECADE => 'DECADE',
        self::DOW => 'DOW',
        self::DOY => 'DOY',
        self::EPOCH => 'EPOCH',
        self::HOUR => 'HOUR',
        self::MICROSECONDS => 'MICROSECONDS',
        self::MILLENIUM => 'MILLENIUM',
        self::MILLISECONDS => 'MILLISECONDS',
        self::MINUTE => 'MINUTE',
        self::MONTH => 'MONTH',
        self::QUARTER => 'QUARTER',
        self::SECOND => 'SECOND',
        self::TIMEZONE => 'TIMEZONE',
        self::TIMEZONE_HOUR => 'TIMEZONE_HOUR',
        self::TIMEZONE_MINUTE => 'TIMEZONE_MINUTE',
        self::WEEK => 'WEEK',
        self::YEAR => 'YEAR'
    ];
}
