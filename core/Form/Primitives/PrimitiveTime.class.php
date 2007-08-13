<?php
/****************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                      *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveTime extends ComplexPrimitive
	{
		const HOURS		= PrimitiveTimestamp::HOURS;
		const MINUTES	= PrimitiveTimestamp::MINUTES;
		const SECONDS	= PrimitiveTimestamp::SECONDS;
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setValue(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);

			$this->value = $time;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setMin(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);

			$this->min = $time;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setMax(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);
			
			$this->max = $time;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setDefault(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);
			
			$this->default = $time;
			
			return $this;
		}
		
		public function importSingle($scope)
		{
			if (
				isset($scope[$this->name])
				&& is_string($scope[$this->name])
			) {
				try {
					$time = new Time($scope[$this->name]);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if ($this->checkLimits($time)) {
					$this->value = $time;
					
					return $this->imported = true;
				}
			}
			
			return false;
		}
		
		public function isEmpty($scope)
		{
			if ($this->getState()->isFalse())
				return $this->isMarriedEmpty($scope);
			
			return empty($scope[$this->name]);
		}
		
		public function importMarried($scope)
		{
			if (!$this->isMarriedEmpty($scope)) {
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$this->name][self::HOURS]))
					$hours = (int) $scope[$this->name][self::HOURS];

				if (isset($scope[$this->name][self::MINUTES]))
					$minutes = (int) $scope[$this->name][self::MINUTES];

				if (isset($scope[$this->name][self::SECONDS]))
					$seconds = (int) $scope[$this->name][self::SECONDS];
				
				try {
					$time = new Time($hours.':'.$minutes.':'.$seconds);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if ($this->checkLimits($time)) {
					try {
						$this->value = $time;
						
						return $this->imported = true;
					} catch (WrongArgumentException $e) {
						$this->value = null;
						
						return false;
					}
				}
			}

			return false;
		}
		
		public function import($scope)
		{
			if ($this->isEmpty($scope)) {
				$this->value = null;
				$this->raw = null;
				return null;
			}

			return parent::import($scope);
		}
		
		public function importValue($value)
		{
			if ($value)
				Assert::isTrue($value instanceof Time);
			else
				return parent::importValue(null);
			
			return
				$this->importSingle(
					array($this->getName() => $value->toString())
				);
		}
		
		private function isMarriedEmpty($scope)
		{
			return empty($scope[$this->name][self::HOURS])
				|| empty($scope[$this->name][self::MINUTES])
				|| empty($scope[$this->name][self::SECONDS]);
		}

		private function checkLimits(Time $time)
		{
			return
				!($this->min && $this->min->toSeconds() > $time->toSeconds())
				&& !($this->max && $this->max->toSeconds() < $time->toSeconds());
		}
	}
?>