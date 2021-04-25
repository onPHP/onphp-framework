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

namespace OnPHP\Core\Base;

use DateTimeZone;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Exception\WrongStateException;

/**
 * Date and time container and utilities.
 *
 * @see Date
 * @ingroup Base
 */
class Timestamp extends Date
{
	/**
	 * @param $timestamp
	 * @param DateTimeZone|null $zone
	 * @return Timestamp
	 * @throws WrongArgumentException
	 */
	public static function create($timestamp, DateTimeZone $zone = null): Timestamp
	{
		return new static($timestamp, $zone);
	}

	/**
	 * @return string
	 */
	public static function now(): string
	{
		return date(static::getFormat());
	}

	/**
	 * @return Timestamp
	 * @throws WrongArgumentException
	 */
	public static function makeNow(): Timestamp
	{
		return new static(time());
	}

	/**
	 * @param $dateTime
	 * @param DateTimeZone|null $zone
	 * @throws WrongArgumentException
	 */
	public function __construct($dateTime, DateTimeZone $zone = null)
	{
		parent::__construct($dateTime);

		if (null !== $zone) {
			$this->dateTime->setTimezone($zone);
		}
	}

	/**
	 * @return DateTimeZone
	 * @throws WrongStateException
	 */
	private function getDefaultTimeZone(): DateTimeZone
	{
		try {
			$defaultTimeZoneName = date_default_timezone_get();
			return new DateTimeZone($defaultTimeZoneName);
		} catch(\Throwable $e) {
			throw new WrongStateException(
				(
					($defaultTimeZoneName ?? false)
						? "strange default time zone given - '{$defaultTimeZoneName}'!"
						: "default time zone not fetched!"
				) . ' Use date_default_timezone_set() for set valid default time zone.'
			);
		}
	}

	/**
	 * @param string $timeDelimiter
	 * @param string $secondDelimiter
	 * @return string
	 */
	public function toTime(string $timeDelimiter = ':', string $secondDelimiter = '.'): string
	{
		return
			$this->getHour()
			.$timeDelimiter
			.$this->getMinute()
			.$secondDelimiter
			.$this->getSecond();
	}

	/**
	 * @param string $dateDelimiter
	 * @param string $timeDelimiter
	 * @param string $secondDelimiter
	 * @return string
	 */
	public function toDateTime(
		string $dateDelimiter = '-',
		string $timeDelimiter = ':',
		string $secondDelimiter = '.'
	): string
	{
		return
			$this->toDate($dateDelimiter).' '
			.$this->toTime($timeDelimiter, $secondDelimiter);
	}

	/**
	 * @return string
	 */
	public function getHour(): string
	{
		return $this->dateTime->format('H');
	}

	/**
	 * @return string
	 */
	public function getMinute(): string
	{
		return $this->dateTime->format('i');
	}

	/**
	 * @return string
	 */
	public function getSecond(): string
	{
		return $this->dateTime->format('s');
	}

	/**
	 * @param Timestamp $timestamp
	 * @return bool
	 */
	public function equals(Timestamp $timestamp): bool
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

	/**
	 * @return false|int
	 */
	public function getHourStartStamp()
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
	 * @param bool $convertToUtc
	 * @return string
	 */
	public function toIsoString(bool $convertToUtc = true): string
	{
		if ($convertToUtc)
			return date('Y-m-d\TH:i:s\Z', $this->dateTime->getTimestamp() - date('Z', $this->dateTime->getTimestamp()));
		else
			return date('Y-m-d\TH:i:sO', $this->dateTime->getTimestamp());
	}

	/**
	 * @return Timestamp
	 * @throws WrongArgumentException
	 */
	public function toTimestamp(): Timestamp
	{
		return $this->spawn();
	}

	/**
	 * @return string
	 */
	protected static function getFormat(): string
	{
		return 'Y-m-d H:i:s';
	}
}