<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Time's container and utilities.
	 * 
	 * @ingroup Types
	**/
	final class Time extends RangedType implements Stringable
	{
		private $hour	= 0;
		private $minute	= 0;
		private $second	= 0;
		
		/**
		 * @return Time
		**/
		public static function create($input = null)
		{
			return new self($input);
		}
		
		/**
		 * @return Time
		**/
		public function setMin(/* Time */ $min)
		{
			Assert::isInstance($min, $this);
			
			if (null !== $this->max)
				Assert::isGreater($this->max->toSeconds(), $min->toSeconds());
			
			$this->min = $min;
			
			return $this;
		}
		
		/**
		 * @return Time
		**/
		public function setMax(/* Time */ $max)
		{
			Assert::isInstance($max, $this);
			
			if (null !== $this->min)
				Assert::isGreater($max->toSeconds(), $this->min->toSeconds());
			
			$this->max = $max;
			
			return $this;
		}
		
		// currently supports '01:23:45', '012345', '1234', '12'
		public function setValue($input)
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
			
			try {
				$this->checkLimits($this->toSeconds());
			} catch (OutOfRangeException $e) {
				$this->value = null;
				throw $e;
			}
			
			return $this;
		}
		
		public function dropValue()
		{
			parent::dropValue();
			
			$this->hour = null;
			$this->minute = null;
			$this->second = null;
			
			return $this;
		}
		
		public function getHour()
		{
			return $this->hour;
		}
		
		/**
		 * @return Time
		**/
		public function setHour($hour)
		{
			$hour = (int) $hour;
			
			Assert::isTrue(
				$hour >= 0 &&
				$hour <= 24,
				'wrong hour specified'
			);
			
			$this->hour = $hour;
			$this->value = null;
			
			return $this;
		}
		
		public function getMinute()
		{
			return $this->minute;
		}
		
		/**
		 * @return Time
		**/
		public function setMinute($minute)
		{
			$minute = (int) $minute;
			
			Assert::isTrue(
				$minute >= 0
				&& $minute <= 60,
				
				'wrong minute specified'
			);
			
			$this->minute = $minute;
			$this->value = null;
			
			return $this;
		}
		
		public function getSecond()
		{
			return $this->second;
		}
		
		/**
		 * @return Time
		**/
		public function setSecond($second)
		{
			$second = (int) $second;
			
			Assert::isTrue(
				$second >= 0
				&& $second <= 60,
				
				'wrong second specified'
			);
			
			$this->second = $second;
			$this->value = null;
			
			return $this;
		}
		
		/// HH:MM
		public function toString($delimiter = ':')
		{
			if ($this->value === null)
				$this->value =
					$this->doublize($this->hour)
					.$delimiter
					.$this->doublize($this->minute);
			
			return $this->value;
		}
		
		/// HH:MM:SS
		public function toFullString($delimiter = ':')
		{
			return
				$this->toString($delimiter)
				.$delimiter.(
					$this->second
						? $this->doublize($this->second)
						: '00'
				);
		}
		
		public function toMinutes()
		{
			return
				($this->hour * 60)
				+ $this->minute
				+ round($this->second / 100, 0);
		}
		
		public function toSeconds()
		{
			return
				($this->hour * 3600)
				+ ($this->minute * 60)
				+ $this->second;
		}
		
		/* void */ protected function checkLimits($value)
		{
			if (
				(
					(null !== ($min = $this->getMin()))
					&& ($value < $min->toSeconds())
				) || (
					(null !== ($max = $this->getMax()))
					&& ($value > $max->toSeconds())
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
		
		private function doublize($int)
		{
			return sprintf('%02d', $int);
		}
	}
?>