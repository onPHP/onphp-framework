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
		
		public function __construct($timestamp)
		{
			if (is_int($timestamp)) { // unix timestamp
				$this->int = $timestamp;
				$this->string = date('Y-m-d H:i:s', $timestamp);
			} elseif (is_string($timestamp)) { 
				$this->string = $timestamp;
				$this->int = strtotime($timestamp);
			}
		}
		
		public static function create($timestamp)
		{
			return new Timestamp($timestamp);
		}

		public function toStamp()
		{
			return $this->int;
		}
		
		public function toDate()
		{
			return date('Y-m-d', strtotime($this->string));
		}
		
		public function toTime()
		{
			return date('H:i.s', strtotime($this->string));
		}
		
		public function toDateTime()
		{
			return date('Y-m-d H:i:s', strtotime($this->string));
		}

		public function getYear()
		{
			return date('Y', strtotime($this->string));
		}

		public function getMonth()
		{
			return date('m', strtotime($this->string));
		}

		public function getDay()
		{
			return date('d', strtotime($this->string));
		}
		
		public function getHour()
		{
			return date('H', strtotime($this->string));
		}
		
		public function getMinute()
		{
			return date('i', strtotime($this->string));
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
				$this->string = date('Y-m-d H:i:s', $time);
				$this->int = $time;
					
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

		public static function now()
		{
			return date('Y-m-d H:i:s');
		}
	}
?>