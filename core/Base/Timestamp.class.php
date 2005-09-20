<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Garmonbozia Research Group                 *
 *   garmonbozia@shadanakar.org                                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class Timestamp
	{
		private $string		= null;
		private $int		= null;
		
		private $year		= null;
		private $month		= null;
		private $day		= null;
		
		private $hour		= null;
		private $minute		= null;
		private $second		= null;
		
		public function __construct($timestamp)
		{
			if (is_int($timestamp)) { // unix timestamp
				$this->int = $timestamp;
				$this->string = date('Y-m-d H:i:s', $timestamp);
			} elseif (is_string($timestamp)) { 
				$this->int = strtotime($timestamp);
				$this->string = date('Y-m-d H:i:s', $this->int);
			} else {
				throw new WrongArgumentException('strange timestamp given');
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
		
		// FIXME: should return Timestamp instance
		public static function now()
		{
			return date('Y-m-d H:i:s');
		}
		
		public static function today($delimiter = '-')
		{
			return date("Y{$delimiter}m{$delimiter}d");
		}
		
		private function import($string)
		{
			list($date, $time) = explode(' ', $string, 2);
			
			list($this->year, $this->month, $this->day) =
				explode('-', $date);
			
			list($this->hour, $this->minute, $this->second) =
				explode(':', $time);
		}
	}
?>
