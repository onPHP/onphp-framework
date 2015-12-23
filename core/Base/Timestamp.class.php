<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Garmonbozia Research Group,                *
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
 * Date and time container and utilities.
 *
 * @see Date
 *
 * @ingroup Base
 **/
class Timestamp extends Date
{

    /**
     * Timestamp constructor.
     *
     * @param $dateTime
     * @param DateTimeZone|null $zone
     */
    public function __construct($dateTime, DateTimeZone $zone = null)
    {
        parent::__construct($dateTime);

        if ($zone) {
            $this->dateTime->setTimezone($zone);
        }

    }

    /**
     * @param $timestamp
     * @param DateTimeZone|null $zone
     * @return Timestamp
     */
    public static function create($timestamp, DateTimeZone $zone = null)
    {
        return new static($timestamp, $zone);
    }

    /**
     * @return bool|string
     */
    public static function now()
    {
        return date(static::getFormat());
    }

    /**
     * @return string
     */
    protected static function getFormat() : string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return Timestamp
     */
    public static function makeNow()
    {
        return new static(time());
    }

    /**
     * @return Timestamp
     **/
    public static function makeToday()
    {
        return new static(static::today());
    }

    /**
     * @param Timestamp $timestamp
     * @return bool
     */
    public function equals(Timestamp $timestamp) : bool
    {
        return ($this->toDateTime() === $timestamp->toDateTime());
    }

    /**
     * @param string $dateDelimiter
     * @param string $timeDelimiter
     * @param string $secondDelimiter
     * @return string
     */
    public function toDateTime($dateDelimiter = '-', $timeDelimiter = ':', $secondDelimiter = '.') : string
    {
        return
            $this->toDate($dateDelimiter) . ' '
            . $this->toTime($timeDelimiter, $secondDelimiter);
    }

    /**
     * @param string $timeDelimiter
     * @param string $secondDelimiter
     * @return string
     */
    public function toTime($timeDelimiter = ':', $secondDelimiter = '.') : string
    {
        return
            $this->getHour()
            . $timeDelimiter
            . $this->getMinute()
            . $secondDelimiter
            . $this->getSecond();
    }

    /**
     * @return string
     */
    public function getHour() : string
    {
        return $this->dateTime->format('H');
    }

    /**
     * @return string
     */
    public function getMinute() : string
    {
        return $this->dateTime->format('i');
    }

    /**
     * @return string
     */
    public function getSecond() : string
    {
        return $this->dateTime->format('s');
    }

    /**
     * @return int
     */
    public function getDayStartStamp() : int
    {
        if (!$this->getHour() && !$this->getMinute() && !$this->getSecond()) {
            return $this->dateTime->getTimestamp();
        } else {
            return parent::getDayStartStamp();
        }
    }

    /**
     * @return int
     */
    public function getHourStartStamp() : int
    {
        if (!$this->getMinute() && !$this->getSecond()) {
            return $this->dateTime->getTimestamp();
        }

        return
            mktime(
                $this->getHour(),
                0,
                0,
                $this->getMonth(),
                $this->getDay(),
                $this->getYear()
            );
    }

    /**
     * ISO 8601 time string
     *
     * @param bool $convertToUtc
     * @return bool|string
     */
    public function toIsoString($convertToUtc = true)
    {
        if ($convertToUtc) {
            return date('Y-m-d\TH:i:s\Z', $this->dateTime->getTimestamp() - date('Z', $this->dateTime->getTimestamp()));
        } else {
            return date('Y-m-d\TH:i:sO', $this->dateTime->getTimestamp());
        }
    }

    /**
     * @return Timestamp
     */
    public function toTimestamp()
    {
        return $this->spawn();
    }

    /**
     * @return DateTimeZone
     * @throws WrongStateException
     */
    private function getDefaultTimeZone()
    {
        $defaultTimeZoneName = date_default_timezone_get();
        try {
            return new DateTimeZone($defaultTimeZoneName);
        } catch (Exception $e) {
            throw new WrongStateException(
                "strange default time zone given - '{$defaultTimeZoneName}'!" .
                'Use date_default_timezone_set() for set valid default time zone.'
            );
        }
    }
}
