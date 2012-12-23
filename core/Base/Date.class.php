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
		const WEEKDAY_MONDAY 	= 1;
		const WEEKDAY_TUESDAY	= 2;
		const WEEKDAY_WEDNESDAY	= 3;
		const WEEKDAY_THURSDAY	= 4;
		const WEEKDAY_FRIDAY	= 5;
		const WEEKDAY_SATURDAY	= 6;
		const WEEKDAY_SUNDAY	= 0; // because strftime('%w') is 0 on Sunday

		/**
		 * @var DateTime
		 */
		protected $dateTime = null;

		/**
		 * @return Date
		**/
		public static function create($date)
		{
			return new static($date);
		}
		
		public static function today($delimiter = '-')
		{
			return date("Y{$delimiter}m{$delimiter}d");
		}
		
		/**
		 * @return Date
		**/
		public static function makeToday()
		{
			return new static(static::today());
		}
		
		/**
		 * @return Date
		 * @see http://www.faqs.org/rfcs/rfc3339.html
		 * @see http://www.cl.cam.ac.uk/~mgk25/iso-time.html
		**/
		public static function makeFromWeek($weekNumber, $year = null)
		{
			if (!$year)
				$year = date('Y');

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
		
		public static function dayDifference(Date $left, Date $right)
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
		
		public static function compare(Date $left, Date $right)
		{
			if ($left->toStamp() == $right->toStamp())
				return 0;
			else
				return ($left->toStamp() > $right->toStamp() ? 1 : -1);
		}

		public static function getWeekCountInYear($year)
		{
			$weekCount = date('W', mktime(0, 0, 0, 12, 31, $year));

			if ($weekCount == '01') {
				return date('W', mktime(0, 0, 0, 12, 24, $year));
			} else {
				return $weekCount;
			}
		}

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

		public function toStamp()
		{
			return $this->getDateTime()->getTimestamp();
		}
		
		public function toDate($delimiter = '-')
		{
			return
				$this->getYear()
				.$delimiter
				.$this->getMonth()
				.$delimiter
				.$this->getDay();
		}
		
		public function getYear()
		{
			return $this->dateTime->format('Y');
		}

		public function getMonth()
		{
			return $this->dateTime->format('m');
		}

		public function getDay()
		{
			return $this->dateTime->format('d');
		}
		
		public function getWeek()
		{
			return date('W', $this->dateTime->getTimestamp());
		}

		public function getWeekDay()
		{
			return strftime('%w', $this->dateTime->getTimestamp());
		}
		
		/**
		 * @return Date
		**/
		public function spawn($modification = null)
		{

			$child = new static($this->toString());
			
			if ($modification)
				return $child->modify($modification);
			
			return $child;
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
		 * @return Date
		**/
		public function getFirstDayOfWeek($weekStart = Date::WEEKDAY_MONDAY)
		{
			return $this->spawn(
				'-'.((7 + $this->getWeekDay() - $weekStart) % 7).' days'
			);
		}
		
		/**
		 * @return Date
		**/
		public function getLastDayOfWeek($weekStart = Date::WEEKDAY_MONDAY)
		{
			return $this->spawn(
				'+'.((13 - $this->getWeekDay() + $weekStart) % 7).' days'
			);
		}
		
		public function toString()
		{
			return $this->dateTime->format(static::getFormat());
		}
		
		public function toFormatString($format)
		{
			return $this->dateTime->format($format);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			// there are no known differences yet
			return $dialect->quoteValue($this->toString());
		}
		
		/**
		 * ISO 8601 date string
		**/
		public function toIsoString()
		{
			return $this->toString();
		}
		
		/**
		 * @return Timestamp
		**/
		public function toTimestamp()
		{
			return Timestamp::create($this->toStamp());
		}

		/**
		 * @return DateTime|null
		 */
		public function getDateTime()
		{
			return $this->dateTime;
		}
		
		protected static function getFormat()
		{
			return 'Y-m-d';
		}


		protected function import($date)
		{
			try{
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


			} catch(Exception $e) {
				throw new WrongArgumentException(
					"strange input given - '{$date}'"
				);
			}

		}
	}

