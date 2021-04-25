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

namespace OnPHP\Core\Base;

use DateTime;
use OnPHP\Core\OSQL\DialectString;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\DB\Dialect;

/**
 * Date's container and utilities.
 * 
 * @see DateRange
 * @ingroup Base
**/
class Date implements Stringable, DialectString
{
	const WEEKDAY_MONDAY 	= 1;
	const WEEKDAY_TUESDAY	= 2;
	const WEEKDAY_WEDNESDAY	= 3;
	const WEEKDAY_THURSDAY	= 4;
	const WEEKDAY_FRIDAY	= 5;
	const WEEKDAY_SATURDAY	= 6;
	const WEEKDAY_SUNDAY	= 0; // because strftime('%w') is 0 on Sunday

	/**
	 * @var DateTime|null
	 */
	protected ?DateTime $dateTime = null;

	/**
	 * @return static
	 * @throws WrongArgumentException
	 */
	public static function create($date): Date
	{
		return new static($date);
	}

	/**
	 * @param string $delimiter
	 * @return string
	 */
	public static function today($delimiter = '-'): string
	{
		return date("Y{$delimiter}m{$delimiter}d");
	}

	/**
	 * @return static
	 * @throws WrongArgumentException
	 */
	public static function makeToday(): Date
	{
		return new static(static::today());
	}

	/**
	 * @see http://www.faqs.org/rfcs/rfc3339.html
	 * @see http://www.cl.cam.ac.uk/~mgk25/iso-time.html
	 * @param $weekNumber
	 * @param int|null $year
	 * @return Date
	 * @throws WrongArgumentException
	 */
	public static function makeFromWeek($weekNumber, int $year = null): Date
	{
		if (null === $year) {
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
	 * @param Date $left
	 * @param Date $right
	 * @return int
	 */
	public static function dayDifference(Date $left, Date $right): int
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
	 * @param Date $left
	 * @param Date $right
	 * @return int
	 */
	public static function compare(Date $left, Date $right): int
	{
		if ($left->toStamp() == $right->toStamp()) {
			return 0;
		} else {
			return ($left->toStamp() > $right->toStamp() ? 1 : -1);
		}
	}

	/**
	 * @param $year
	 * @return int
	 */
	public static function getWeekCountInYear($year): int
	{
		$weekCount = date('W', mktime(0, 0, 0, 12, 31, $year));

		if ($weekCount == '01') {
			return date('W', mktime(0, 0, 0, 12, 24, $year));
		} else {
			return $weekCount;
		}
	}

	/**
	 * @param $date
	 * @throws WrongArgumentException
	 */
	public function __construct($date)
	{
		$this->import($date);
	}

	public function __clone()
	{
		$this->dateTime = clone $this->dateTime;
	}

	public function  __sleep()
	{
		return array('dateTime');
	}

	/**
	 * @return int
	 */
	public function toStamp(): int
	{
		return $this->getDateTime()->getTimestamp();
	}

	/**
	 * @param string $delimiter
	 * @return string
	 */
	public function toDate($delimiter = '-'): string
	{
		return
			$this->getYear()
			.$delimiter
			.$this->getMonth()
			.$delimiter
			.$this->getDay();
	}

	/**
	 * @return string
	 */
	public function getYear(): string
	{
		return $this->dateTime->format('Y');
	}

	/**
	 * @return string
	 */
	public function getMonth(): string
	{
		return $this->dateTime->format('m');
	}

	/**
	 * @return string
	 */
	public function getDay(): string
	{
		return $this->dateTime->format('d');
	}

	/**
	 * @return string
	 */
	public function getWeek(): string
	{
		return date('W', $this->dateTime->getTimestamp());
	}

	/**
	 * @return string
	 */
	public function getWeekDay(): string
	{
		return strftime('%w', $this->dateTime->getTimestamp());
	}

	/**
	 * @param string|null $modification
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function spawn(string $modification = null): Date
	{
		$child = new static($this->toString());

		if (null !== $modification) {
			return $child->modify($modification);
		}

		return $child;
	}

	/**
	 * @param string $string
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function modify(string $string): Date
	{
		try {
			$this->dateTime->modify($string);
		} catch (\Throwable $e) {
			throw new WrongArgumentException("wrong time string '{$string}'");
		}

		return $this;
	}

	/**
	 * @return false|int
	 */
	public function getDayStartStamp()
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
	 * @return false|int
	 */
	public function getDayEndStamp()
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
	 * @throws WrongArgumentException
	 */
	public function getFirstDayOfWeek(int $weekStart = Date::WEEKDAY_MONDAY): Date
	{
		return $this->spawn(
			'-'.((7 + $this->getWeekDay() - $weekStart) % 7).' days'
		);
	}

	/**
	 * @param int $weekStart
	 * @return Date
	 * @throws WrongArgumentException
	 */
	public function getLastDayOfWeek(int $weekStart = Date::WEEKDAY_MONDAY): Date
	{
		return $this->spawn(
			'+'.((13 - $this->getWeekDay() + $weekStart) % 7).' days'
		);
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->dateTime->format(static::getFormat());
	}

	/**
	 * @param string $format
	 * @return string
	 */
	public function toFormatString(string $format): string
	{
		return $this->dateTime->format($format);
	}

	/**
	 * @param Dialect $dialect
	 * @return string
	 */
	public function toDialectString(Dialect $dialect): string
	{
		return $dialect->quoteValue($this->toString());
	}

	/**
	 * ISO 8601 date string
	 * @return string
	 */
	public function toIsoString(): string
	{
		return $this->toString();
	}

	/**
	 * @return Timestamp
	 * @throws WrongArgumentException
	 */
	public function toTimestamp(): Timestamp
	{
		return Timestamp::create($this->toStamp());
	}

	/**
	 * @return DateTime|null
	 */
	public function getDateTime(): ?DateTime
	{
		return $this->dateTime;
	}

	/**
	 * @return string
	 */
	protected static function getFormat(): string
	{
		return 'Y-m-d';
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
						checkdate($matches[2], $matches[1], $matches[3])
					);
				}

				$this->dateTime = new DateTime($date);
			}
		} catch(\Throwable $e) {
			throw new WrongArgumentException("strange input given - '{$date}'");
		}
	}
}
