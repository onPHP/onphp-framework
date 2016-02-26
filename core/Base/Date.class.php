<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Garmonbozia Research Group                 *
 *   Anton E. Lebedevich, Konstantin V. Arkhipov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/**
 * Date's container and utilities.
 *
 * @see DateRange
 *
 * @ingroup Base
 **/
class Date implements Stringable, DialectString
{
    const
        WEEKDAY_MONDAY = 1,
        WEEKDAY_TUESDAY = 2,
        WEEKDAY_WEDNESDAY = 3,
        WEEKDAY_THURSDAY = 4,
        WEEKDAY_FRIDAY = 5,
        WEEKDAY_SATURDAY = 6,
        WEEKDAY_SUNDAY = 0; // because strftime('%w') is 0 on Sunday

    /**
     * @var DateTime
     */
    protected $dateTime = null;

    /**
     * Date constructor.
     * @param $date
     */
    public function __construct($date)
    {
        $this->import($date);
    }

    /**
     * @param $date
     * @throws WrongArgumentException
     */
    protected function import($date)
    {
        try {
            if (is_int($date) || is_numeric($date)) { // unix timestamp
                $this->dateTime = new DateTime(date(static::getFormat(), $date));

            } elseif ($date && is_string($date)) {

                if (
                preg_match('/^(\d{1,4})[-\.](\d{1,2})[-\.](\d{1,2})/', $date, $matches)
                ) {
                    Assert::isTrue(
                        checkdate($matches[2], $matches[3], $matches[1])
                    );
                } elseif (
                preg_match('/^(\d{1,2})[-\.](\d{1,2})[-\.](\d{1,4})/', $date, $matches)
                ) {
                    Assert::isTrue(
                        checkdate($matches[2], $matches[2], $matches[3])
                    );
                }

                $this->dateTime = new DateTime($date);
            }


        } catch (Exception $e) {
            throw new WrongArgumentException(
                "strange input given - '{$date}'"
            );
        }

    }

    /**
     * @return string
     */
    protected static function getFormat() : string
    {
        return 'Y-m-d';
    }

    /**
     * @return Date
     **/
    public static function makeToday()
    {
        return new static(static::today());
    }

    /**
     * @param string $delimiter
     * @return bool|string
     */
    public static function today($delimiter = '-')
    {
        return date("Y{$delimiter}m{$delimiter}d");
    }

    /**
     * @return Date
     * @see http://www.faqs.org/rfcs/rfc3339.html
     * @see http://www.cl.cam.ac.uk/~mgk25/iso-time.html
     **/
    public static function makeFromWeek($weekNumber, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        Assert::isTrue(
            ($weekNumber > 0)
            && ($weekNumber <= static::getWeekCountInYear($year))
        );

        $date =
            new static(
                date(
                    static::getFormat(),
                    mktime(
                        0, 0, 0, 1, 1, $year
                    )
                )
            );

        $days =
            (
                (
                    $weekNumber - 1
                    + (static::getWeekCountInYear($year - 1) == 53 ? 1 : 0)
                )
                * 7
            ) + 1 - $date->getWeekDay();

        return $date->modify("+{$days} day");
    }

    /**
     * @param $year
     * @return bool|string
     */
    public static function getWeekCountInYear($year)
    {
        $weekCount = date('W', mktime(0, 0, 0, 12, 31, $year));

        if ($weekCount == '01') {
            return date('W', mktime(0, 0, 0, 12, 24, $year));
        } else {
            return $weekCount;
        }
    }

    /**
     * @return string
     */
    public function getWeekDay() : string
    {
        return strftime('%w', $this->dateTime->getTimestamp());
    }

    /**
     * @throws WrongArgumentException
     * @return Date
     **/
    public function modify($string)
    {
        try {
            $this->dateTime->modify($string);
        } catch (Exception $e) {
            throw new WrongArgumentException(
                "wrong time string '{$string}'"
            );
        }

        return $this;
    }

    /**
     * @param Date $left
     * @param Date $right
     * @return integer
     */
    public static function dayDifference(Date $left, Date $right) : int
    {
        return
            gregoriantojd(
                $right->getMonth(),
                $right->getDay(),
                $right->getYear()
            )
            - gregoriantojd(
                $left->getMonth(),
                $left->getDay(),
                $left->getYear()
            );
    }

    /**
     * @return string
     */
    public function getMonth() : string
    {
        return $this->dateTime->format('m');
    }

    /**
     * @return string
     */
    public function getDay() : string
    {
        return $this->dateTime->format('d');
    }

    /**
     * @return string
     */
    public function getYear() : string
    {
        return $this->dateTime->format('Y');
    }

    /**
     * @param Date $left
     * @param Date $right
     * @return integer
     */
    public static function compare(Date $left, Date $right) : int
    {
        if ($left->toStamp() == $right->toStamp()) {
            return 0;
        } else {
            return ($left->toStamp() > $right->toStamp() ? 1 : -1);
        }
    }

    /**
     * @return integer
     */
    public function toStamp() : int
    {
        return $this->getDateTime()->getTimestamp();
    }

    /**
     * @return DateTime|null
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @see clone
     */
    public function __clone()
    {
        $this->dateTime = clone $this->dateTime;
    }

    /**
     * @see sleep
     * @return array
     */
    public function __sleep()
    {
        return ['dateTime'];
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function toDate($delimiter = '-') : string
    {
        return
            $this->getYear()
            . $delimiter
            . $this->getMonth()
            . $delimiter
            . $this->getDay();
    }

    /**
     * @return bool|string
     */
    public function getWeek()
    {
        return date('W', $this->dateTime->getTimestamp());
    }

    /**
     * @return integer
     */
    public function getDayStartStamp() : int
    {
        return
            mktime(
                0, 0, 0,
                $this->getMonth(),
                $this->getDay(),
                $this->getYear()
            );
    }

    /**
     * @return integer
     */
    public function getDayEndStamp() : int
    {
        return
            mktime(
                23, 59, 59,
                $this->getMonth(),
                $this->getDay(),
                $this->getYear()
            );
    }

    /**
     * @param int $weekStart
     * @return Date
     */
    public function getFirstDayOfWeek($weekStart = Date::WEEKDAY_MONDAY) : Date
    {
        return $this->spawn(
            '-' . ((7 + (integer) $this->getWeekDay() - $weekStart) % 7) . ' days'
        );
    }

    /**
     * @param null $modification
     * @return Date
     * @throws WrongArgumentException
     */
    public function spawn($modification = null) : Date
    {

        $child = new static($this->toString());

        if ($modification) {
            return $child->modify($modification);
        }

        return $child;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->dateTime->format(static::getFormat());
    }

    /**
     * @param int $weekStart
     * @return Date
     */
    public function getLastDayOfWeek($weekStart = Date::WEEKDAY_MONDAY) : Date
    {
        return $this->spawn(
            '+' . ((13 - $this->getWeekDay() + $weekStart) % 7) . ' days'
        );
    }

    /**
     * @param $format
     * @return string
     */
    public function toFormatString($format) : string
    {
        return $this->dateTime->format($format);
    }

    /**
     * @param Dialect $dialect
     * @return mixed
     */
    public function toDialectString(Dialect $dialect)
    {
        // there are no known differences yet
        return $dialect->quoteValue($this->toString());
    }

    /**
     * ISO 8601 date string
     *
     * @return string
     */
    public function toIsoString()
    {
        return $this->toString();
    }

    /**
     * @return Timestamp
     **/
    public function toTimestamp()
    {
        return new Timestamp($this->toStamp());
    }
}