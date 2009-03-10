<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Garmonbozia Research Group,                *
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
	 * @ingroup Types
	**/
	class Date extends RangedType implements Stringable, DialectString
	{
		const WEEKDAY_MONDAY 	= 1;
		const WEEKDAY_TUESDAY	= 2;
		const WEEKDAY_WEDNESDAY	= 3;
		const WEEKDAY_THURSDAY	= 4;
		const WEEKDAY_FRIDAY	= 5;
		const WEEKDAY_SATURDAY	= 6;
		const WEEKDAY_SUNDAY	= 0; // because strftime('%w') is 0 on Sunday
		
		protected $int		= 0;
		
		protected $year		= '0000';
		protected $month	= 1;
		protected $day		= 1;
		
		/**
		 * @return Date
		**/
		public static function create($date = null)
		{
			return new self($date);
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
			return new self(self::today());
		}
		
		/**
		 * @return Date
		**/
		public static function makeFromWeek($weekNumber, $year = null)
		{
			if (!$year)
				$year = date('Y');
			
			Assert::isTrue(
				($weekNumber > 0)
				&& ($weekNumber < 53)
			);
			
			$date =
				new self(
					date(
						self::getFormat(),
						mktime(
							0, 0, 0, 1, 1, $year
						)
					)
				);
			
			$days = (($weekNumber - 1) * 7) + 1 - $date->getWeekDay();
			
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
			if ($left->int == $right->int)
				return 0;
			else
				return ($left->int > $right->int ? 1 : -1);
		}
		
		/**
		 * @return Date
		**/
		public function setMin(/* Date */ $min)
		{
			if (null !== $this->max)
				Assert::isEqual($this->compare($min, $this->max), -1);
			
			$this->min = $min;
			
			return $this;
		}
		
		/**
		 * @return Date
		**/
		public function setMax(/* Date */ $max)
		{
			if (null !== $this->min)
				Assert::isEqual($this->compare($this->min, $max), -1);
			
			$this->max = $max;
			
			return $this;
		}
		
		public function setValue($date)
		{
			$value = null;
			
			if (is_int($date) || is_numeric($date)) { // unix timestamp
				$value = date($this->getFormat(), $date);
			} elseif ($date && is_string($date))
				$value = $this->stringImport($date);
			
			if (null === $value) {
				throw new WrongArgumentException(
					"strange input given - '{$date}'"
				);
			}
			
			$this->import($value);
			
			try {
				$this->checkLimits($this->value);
			} catch (OutOfRangeException $e) {
				$this->value = null;
				throw $e;
			}
			
			return $this;
		}
		
		public function toStamp()
		{
			return $this->int;
		}
		
		public function toDate($delimiter = '-')
		{
			return
				$this->year
				.$delimiter
				.$this->month
				.$delimiter
				.$this->day;
		}
		
		public function getYear()
		{
			return $this->year;
		}

		public function getMonth()
		{
			return $this->month;
		}

		public function getDay()
		{
			return $this->day;
		}
		
		public function getWeek()
		{
			return date('W', $this->int);
		}

		public function getWeekDay()
		{
			return strftime('%w', $this->int);
		}
		
		/**
		 * @return Date
		**/
		public function spawn($modification = null)
		{
			$child = new $this($this->value);
			
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
				$time = strtotime($string, $this->int);
				
				if ($time === false)
					throw new WrongArgumentException(
						"modification yielded false"
					);
				
				$this->int = $time;
				$this->value = date($this->getFormat(), $time);
				$this->import($this->value);
			} catch (BaseException $e) {
				throw new WrongArgumentException(
					"wrong time string '{$string}': {$e->getMessage()}"
				);
			}
			
			return $this;
		}
		
		public function getDayStartStamp()
		{
			return
				mktime(
					0, 0, 0,
					$this->month,
					$this->day,
					$this->year
				);
		}
		
		public function getDayEndStamp()
		{
			return
				mktime(
					23, 59, 59,
					$this->month,
					$this->day,
					$this->year
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
			return $this->value;
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
		
		protected static function getFormat()
		{
			return 'Y-m-d';
		}
		
		/* void */ protected function import($string)
		{
			list($this->year, $this->month, $this->day) =
				explode('-', $string, 3);
			
			if (!$this->month || !$this->day)
				throw new WrongArgumentException(
					'month and day must not be zero'
				);
			
			$this->value =
				sprintf(
					'%04d-%02d-%02d',
					$this->year,
					$this->month,
					$this->day
				);
			
			$this->buildInteger();
			
			list($this->year, $this->month, $this->day) =
				explode('-', $this->value, 3);
		}
		
		/* void */ protected function stringImport($string)
		{
			$matches = array();
			
			if (
				preg_match('/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/', $string, $matches)
			) {
				if (checkdate($matches[2], $matches[3], $matches[1]))
					return $string;
				
			} elseif (($stamp = strtotime($string)) !== false)
				return date($this->getFormat(), $stamp);
		}
		
		/* void */ protected function checkLimits(/* Date */ $value)
		{
			if (
				(
					(null !== ($min = $this->getMin()))
					&& ($this->compare($min, $value))
				) || (
					(null !== ($max = $this->getMax()))
					&& ($this->compare($value, $max))
				)
			) {
				throw new OutOfRangeException(
					Assert::dumpArgument($value).' exceeds defined range: '
					.Assert::dumpArgument($min)
					.' - '
					// can be undefined
					.Assert::dumpArgument($this->getMax())
				);
			}
		}
		
		/* void */ protected function buildInteger()
		{
			$this->int =
				mktime(
					0, 0, 0,
					$this->month,
					$this->day,
					$this->year
				);
		}
	}
?>