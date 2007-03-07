<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Garmonbozia Research Group,                *
 *   Anton E. Lebedevich, Konstantin V. Arkhipov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
		private $hour		= null;
		private $minute		= null;
		private $second		= null;

		/**
		 * @return Timestamp
		**/
		public static function create($timestamp)
		{
			return new self($timestamp);
		}
		
		public static function compare(Date $left, Date $right)
		{
			if ($left->int == $right->int)
				return 0;
			else
				return ($left->int > $right->int ? 1 : -1);
		}
		
		public static function now()
		{
			return date(self::getFormat());
		}
		
		/**
		 * @return Timestamp
		**/
		public static function makeNow()
		{
			return new self(time());
		}
		
		/**
		 * @return Timestamp
		**/
		public static function makeToday()
		{
			return new self(self::today());
		}
		
		public function __construct($timestamp)
		{
			if (Assert::checkInteger($timestamp)) { // unix timestamp
				$this->int = $timestamp;
				$this->string = date($this->getFormat(), $timestamp);
			} elseif (is_string($timestamp)) { 
				$this->int = strtotime($timestamp);
				
				if (
					preg_match(
						'/^\d{1,4}-\d{1,2}-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}$/',
						$timestamp
					)
				) {
					$this->string = $timestamp;
				} elseif (preg_match('/^\d{1,4}-\d{1,2}-\d{1,2}$/', $timestamp))
					$this->string = $timestamp . ' 00:00:00';
				else
					$this->string = date($this->getFormat(), $this->int);
			} else {
				throw new WrongArgumentException(
					"strange timestamp given - '{$timestamp}'"
				);
			}
			
			$this->import($this->string);
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

		public function getHour()
		{
			return $this->hour;
		}
		
		public function getMinute()
		{
			return $this->minute;
		}
		
		public function getSecond()
		{
			return $this->second;
		}
		
		public function equals(Timestamp $timestamp)
		{
			return ($this->toDateTime() === $timestamp->toDateTime());
		}
		
		public function getDayStartStamp()
		{
			if (!$this->hour && !$this->minute && !$this->second)
				return $this->int;
			else
				return parent::getDayStartStamp();
		}

		protected static function getFormat()
		{
			return 'Y-m-d H:i:s';
		}
		
		/* void */ protected function import($string)
		{
			list($date, $time) = explode(' ', $string, 2);
			
			parent::import($date);
			
			list($this->hour, $this->minute, $this->second) =
				explode(':', $time, 3);
			
			$time =
				sprintf(
					'%02d:%02d:%02d',
					$this->hour,
					$this->minute,
					$this->second
				);
			
			list($this->hour, $this->minute, $this->second) =
				explode(':', $time, 3);
			
			$this->string .= ' '.$time;
		}
	}
?>