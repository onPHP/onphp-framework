<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Garmonbozia Research Group,                *
 *   Anton E. Lebedevich, Konstantin V. Arkhipov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
	class Timestamp implements Stringable
	{
		const WEEKDAY_MONDAY 	= 1;
		const WEEKDAY_TUESDAY	= 2;
		const WEEKDAY_WEDNESDAY	= 3;
		const WEEKDAY_THURSDAY	= 4;
		const WEEKDAY_FRIDAY	= 5;
		const WEEKDAY_SATURDAY	= 6;
		const WEEKDAY_SUNDAY	= 0; // because strftime('%w') is 0 on Sunday
		
		private $string		= null;
		private $int		= null;
		
		private $year		= null;
		private $month		= null;
		private $day		= null;
		
		private $hour		= null;
		private $minute		= null;
		private $second		= null;

		public static function create($timestamp)
		{
			return new Timestamp($timestamp);
		}
		
		public static function compare(Timestamp $left, Timestamp $right)
		{
			if ($left->int == $right->int)
				return 0;
			else
				return ($left->int > $right->int ? 1 : -1);
		}
		
		public static function now()
		{
			return date('Y-m-d H:i:s');
		}
		
		public static function makeNow()
		{
			return new self(time());
		}
		
		public static function today($delimiter = '-')
		{
			return date("Y{$delimiter}m{$delimiter}d");
		}
		
		public static function makeToday()
		{
			return new self(self::today());
		}
		
		public static function dayDifference(Timestamp $left, Timestamp $right)
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
		
		public function __construct($timestamp)
		{
			if (is_int($timestamp)) { // unix timestamp
				$this->int = $timestamp;
				$this->string = date('Y-m-d H:i:s', $timestamp);
			} elseif (is_string($timestamp)) {
				$this->int = strtotime($timestamp);
				
				$matches = array();
				
				if (
					preg_match(
						'/^(\d{1,4})-(\d{1,2})-(\d{1,2})\s\d{1,2}:\d{1,2}:\d{1,2}$/',
						$timestamp,
						$matches
					)
				) {
					if (checkdate($matches[2], $matches[3], $matches[1]))
						$this->string = $timestamp;
				} elseif (preg_match('/^\d{1,4}-\d{1,2}-\d{1,2}$/', $timestamp))
					$this->string = $timestamp . ' 00:00:00';
				else
					$this->string = date('Y-m-d H:i:s', $this->int);
			} else {
				throw new WrongArgumentException(
					"strange timestamp given - '{$timestamp}'"
				);
			}
			
			$this->import($this->string);
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
		
		public function toTime($timeDelimiter = ':', $secondDelimiter = '.')
		{
			return
				$this->hour
				.$timeDelimiter
				.$this->minute
				.$secondDelimiter
				.$this->second;
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

		public function getWeekDay()
		{
			return strftime('%w', $this->int);
		}
		
		public function getHour()
		{
			return $this->hour;
		}
		
		public function getMinute()
		{
			return $this->minute;
		}
		
		public function spawn($modification = null)
		{
			$child = new Timestamp($this->string);
			
			if ($modification)
				return $child->modify($modification);
			else
				return $child;
		}
		
		public function modify($string)
		{
			try {
				$time = strtotime($string, $this->int);
				
				if ($time === false)
					throw new WrongArgumentException(
						"modification yielded false '{$string}'"
					);
				
				$this->int = $time;
				$this->string = date('Y-m-d H:i:s', $time);
				$this->import($this->string);

				return $this;
			} catch (BaseException $e) {
				throw new WrongArgumentException(
					"wrong time string '{$string}'"
				);
			}
		}
		
		public function equals(Timestamp $timestamp)
		{
			return ($this->toDateTime() === $timestamp->toDateTime());
		}
		
		public function toString()
		{
			return $this->string;
		}
		
		public function getDayStartStamp()
		{
			if (!$this->hour && !$this->minute && !$this->second)
				return $this->int;
			else
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
		
		public function getFirstDayOfWeek($weekStart = Timestamp::WEEKDAY_MONDAY)
		{
			return $this->spawn(
				'-'.((7 + $this->getWeekDay() - $weekStart) % 7).' days'
			);
		}
		
		public function getLastDayOfWeek($weekStart = Timestamp::WEEKDAY_MONDAY)
		{
			return $this->spawn(
				'+'.((13 - $this->getWeekDay() + $weekStart) % 7).' days'
			);
		}
		
		private function import($string)
		{
			list($date, $time) = explode(' ', $string, 2);
			
			list($this->year, $this->month, $this->day) =
				explode('-', $date, 3);
			
			list($this->hour, $this->minute, $this->second) =
				explode(':', $time, 3);
			
			$this->normalizeSelf();
		}
		
		private function normalizeSelf()
		{
			if (!$this->month || !$this->day)
				throw new WrongArgumentException(
					'month and day must not be zero'
				);
			
			if (strlen($this->year) < 4)
				$this->year = str_pad($this->year, 4, '0', STR_PAD_LEFT);
			
			if (strlen($this->month) < 2)
				$this->month = str_pad($this->month, 2, '0', STR_PAD_LEFT);
			
			if (strlen($this->day) < 2)
				$this->day = str_pad($this->day, 2, '0', STR_PAD_LEFT);
			
			if (strlen($this->hour) < 2)
				$this->hour = str_pad($this->hour, 2, '0', STR_PAD_LEFT);
			
			if (strlen($this->minute) < 2)
				$this->minute = str_pad($this->minute, 2, '0', STR_PAD_LEFT);
			
			if (strlen($this->second) < 2)
				$this->second = str_pad($this->second, 2, '0', STR_PAD_LEFT);
		}
	}
?>