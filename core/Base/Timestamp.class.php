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
	 * @return Timestamp
	 **/
	public static function create($timestamp, DateTimeZone $zone=null)
	{
		return new static($timestamp, $zone);
	}

	public static function now()
	{
		return date(static::getFormat());
	}

	/**
	 * @return Timestamp
	 **/
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

	public function __construct($dateTime, DateTimeZone $zone=null)
	{
		parent::__construct($dateTime);

		if($zone) {
			$this->dateTime->setTimezone($zone);
		}

	}

	private function getDefaultTimeZone()
	{
		$defaultTimeZoneName = date_default_timezone_get();
		try {
			return new DateTimeZone($defaultTimeZoneName);
		} catch(Exception $e) {
			throw new WrongStateException(
				"strange default time zone given - '{$defaultTimeZoneName}'!".
				'Use date_default_timezone_set() for set valid default time zone.'
			);
		}
	}

	public function toTime($timeDelimiter = ':', $secondDelimiter = '.')
	{
		return
			$this->getHour()
			.$timeDelimiter
			.$this->getMinute()
			.$secondDelimiter
			.$this->getSecond();
	}

	public function toDateTime(
		$dateDelimiter = '-',
		$timeDelimiter = ':',
		$secondDelimiter = '.'
	)
	{
		return
			$this->toDate($dateDelimiter).' '
			.$this->toTime($timeDelimiter, $secondDelimiter);
	}

	public function getHour()
	{
		return $this->dateTime->format('H');
	}

	public function getMinute()
	{
		return $this->dateTime->format('i');
	}

	public function getSecond()
	{
		return $this->dateTime->format('s');
	}

	public function equals(Timestamp $timestamp)
	{
		return ($this->toDateTime() === $timestamp->toDateTime());
	}

	public function getDayStartStamp()
	{
		if (!$this->getHour() && !$this->getMinute() && !$this->getSecond())
			return $this->dateTime->getTimestamp();
		else
			return parent::getDayStartStamp();
	}

	public function getHourStartStamp()
	{
		if (!$this->getMinute() && !$this->getSecond())
			return $this->dateTime->getTimestamp();

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
	 **/
	public function toIsoString($convertToUtc = true)
	{
		if ($convertToUtc)
			return date('Y-m-d\TH:i:s\Z', $this->dateTime->getTimestamp() - date('Z', $this->dateTime->getTimestamp()));
		else
			return date('Y-m-d\TH:i:sO', $this->dateTime->getTimestamp());
	}

	/**
	 * @return Timestamp
	 **/
	public function toTimestamp()
	{
		return $this->spawn();
	}

	protected static function getFormat()
	{
		return 'Y-m-d H:i:s';
	}
}
?>