<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Time's container and utilities.
	 * 
	 * @ingroup Base
	**/
	class Time implements Stringable
	{
		protected $hour		= 0;
		protected $minute	= 0;
		protected $second	= 0;
		
		private $string		= null;
		
		public static function create($input)
		{
			return new Time($input);
		}
		
		// currently supports '01:23:45', '012345', '1234', '12'
		public function __construct($input)
		{
			if (Assert::checkInteger($input)) {
				$time = $input;
			} else {
				Assert::isString($input);
				$time = explode(':', $input);
			}
			
			$lenght = strlen($input);
			
			if (count($time) === 2) {
				$this->
					setHour($time[0])->
					setMinute($time[1]);
			} elseif (count($time) === 3) {
				$this->
					setHour($time[0])->
					setMinute($time[1])->
					setSecond($time[2]);
			} else {
				switch ($lenght) {
					case 1:
					case 2:
						
						$this->setHour(substr($input, 0, 2));
						break;
						
					case 3:
						
						$assumedHour = substr($input, 0, 2);
						
						if ($assumedHour > 23)
							$this->
								setHour(substr($input, 0, 1))->
								setMinute(substr($input, 1, 2));
						else
							$this->
								setHour($assumedHour)->
								setMinute(substr($input, 2, 1).'0');
						
						break;
					
					case 4:
					case 5:
					case 6:
						
						$this->
							setHour(substr($input, 0, 2))->
							setMinute(substr($input, 2, 2))->
							setSecond(substr($input, 4, 2));
						
						break;
						
					default:
						throw new WrongArgumentException('unknown format');
				}
			}
		}
		
		public function getHour()
		{
			return $this->hour;
		}
		
		public function setHour($hour)
		{
			$hour = (int) $hour;
			
			Assert::isTrue(
				$hour >= 0 &&
				$hour <= 24,
				"wrong hour specified"
			);
			
			$this->hour = $hour;
			$this->string = null;

			return $this;
		}
		
		public function getMinute()
		{
			return $this->minute;
		}
		
		public function setMinute($minute)
		{
			$minute = (int) $minute;
			
			Assert::isTrue(
				$minute >= 0  &&
				$minute <= 60,
				"wrong minute specified"
			);
			
			$this->minute = $minute;
			$this->string = null;
			
			return $this;
		}
		
		public function getSecond()
		{
			return $this->second;
		}
		
		public function setSecond($second)
		{
			$second = (int) $second;
			
			Assert::isTrue(
				$second >= 0 &&
				$second <= 60
			);
			
			$this->second = $second;
			$this->string = null;
			
			return $this;
		}
		
		public function toString($delimiter = ':')
		{
			if ($this->string === null)
				$this->string = $this->doublize($this->hour).$delimiter
					.$this->doublize($this->minute);

			return $this->string;
		}

		public function toFullString($delimiter = ':')
		{
			return
				$this->toString($delimiter).
				(
					$this->second
						? $delimiter.$this->doublize($this->second)
						: $delimiter.'00'
				);
		}

		private function doublize($int)
		{
			return
				$int <= 9
					? "0{$int}"
					: $int;
		}
	}
?>