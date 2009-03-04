<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Garmonbozia Research Group,                *
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
	 * @ingroup Types
	**/
	class Timestamp extends Date
	{
		private $hour		= 0;
		private $minute		= 0;
		private $second		= 0;
		
		/**
		 * @return Timestamp
		**/
		public static function create($timestamp = null)
		{
			return new self($timestamp);
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
			return new self(self::now());
		}
		
		/**
		 * @return Timestamp
		**/
		public static function makeToday()
		{
			return new self(self::today());
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
		
		/**
		 * ISO 8601 time string
		**/
		public function toIsoString($convertToUtc = true)
		{
			if ($convertToUtc)
				return date('Y-m-d\TH:i:s\Z', $this->int - date('Z', $this->int));
			else
				return date('Y-m-d\TH:i:sO', $this->int);
		}
		
		/**
		 * @return Timestamp
		**/
		public function toTimestamp()
		{
			return $this;
		}
		
		protected static function getFormat()
		{
			return 'Y-m-d H:i:s';
		}
		
		public function dropValue()
		{
			parent::dropValue();
			
			$this->hour = null;
			$this->minute = null;
			$this->second = null;
			
			return $this;
		}
		
		/* void */ protected function import($string)
		{
			list($date, $time) = explode(' ', $string, 2);
			
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
			
			parent::import($date);
			
			$this->value .= ' '.$time;
		}
		
		/* void */ protected function stringImport($string)
		{
			$matches = array();
			
			if (
				preg_match(
					'/^(\d{1,4})-(\d{1,2})-(\d{1,2})\s\d{1,2}:\d{1,2}:\d{1,2}$/',
					$string,
					$matches
				)
			) {
				if (checkdate($matches[2], $matches[3], $matches[1]))
					return $string;
			} elseif (preg_match('/^\d{1,4}-\d{1,2}-\d{1,2}$/', $string))
				return $string . ' 00:00:00';
			elseif (($stamp = strtotime($string)) !== false)
				return date($this->getFormat(), $integer);
		}
		
		/* void */ protected function buildInteger()
		{
			$this->int =
				mktime(
					$this->hour,
					$this->minute,
					$this->second,
					$this->month,
					$this->day,
					$this->year
				);
		}
	}
?>