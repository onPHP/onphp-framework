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
		private $string		= null;
		private $int		= null;
		
		private $year		= null;
		private $month		= null;
		private $day		= null;
		
		private $hour		= null;
		private $minute		= null;
		private $second		= null;

		const WEEKDAY_MONDAY 	= 1;
		const WEEKDAY_TUESDAY	= 2;
		const WEEKDAY_WEDNESDAY	= 3;
		const WEEKDAY_THURSDAY	= 4;
		const WEEKDAY_FRIDAY	= 5;
		const WEEKDAY_SATURDAY	= 6;
		const WEEKDAY_SUNDAY	= 0; // because strftime('%w') is 0 on Sunday
		
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
				} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $timestamp))
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
		
		public static function create($timestamp)
		{
			return new Timestamp($timestamp);
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
		
		/**
		 * @todo break API by returning Timestamp instance instead
		**/
		public static function now()
		{
			return date('Y-m-d H:i:s');
		}
		
		public static function today($delimiter = '-')
		{
			return date("Y{$delimiter}m{$delimiter}d");
		}

		public function getDayStartStamp()
		{
			if (!$this->hour && !$this->minute && !$this->second)
				return $this->int;
			else
				return mktime(
						0, 0, 0,
						$this->month,
						$this->day,
						$this->year
					);
		}

		public function getDayEndStamp()
		{
			return mktime(
					23, 59, 59,
					$this->month,
					$this->day,
					$this->year
				);
		}
		
		private function import($string)
		{
			list($date, $time) = explode(' ', $string, 2);
			
			list($this->year, $this->month, $this->day) =
				explode('-', $date, 3);
			
			if (!$this->month || !$this->day)
				throw new WrongArgumentException(
					'month and day must not be zero'
				);
			
			list($this->hour, $this->minute, $this->second) =
				explode(':', $time, 3);
		}

		public static function compare(Timestamp $left, Timestamp $right)
		{
			if ($left->int == $right->int)
				return 0;
			else
				return ($left->int > $right->int ? 1 : -1);
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
	}
?>