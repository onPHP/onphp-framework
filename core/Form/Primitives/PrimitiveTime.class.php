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

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	final class PrimitiveTime extends ComplexPrimitive
	{
		const HOURS		= PrimitiveTimestamp::HOURS;
		const MINUTES	= PrimitiveTimestamp::MINUTES;
		const SECONDS	= PrimitiveTimestamp::SECONDS;
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\PrimitiveTime
		**/
		public function setValue(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);

			$this->value = $time;
			
			return $this;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\PrimitiveTime
		**/
		public function setMin(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);

			$this->min = $time;
			
			return $this;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\PrimitiveTime
		**/
		public function setMax(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);
			
			$this->max = $time;
			
			return $this;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\PrimitiveTime
		**/
		public function setDefault(/* Time */ $time)
		{
			Assert::isTrue($time instanceof Time);
			
			$this->default = $time;
			
			return $this;
		}
		
		public function importSingle($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			try {
				$time = new Time($scope[$this->name]);
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			if ($this->checkLimits($time)) {
				$this->value = $time;
				
				return true;
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
			if (
				BasePrimitive::import($scope)
				&& is_array($scope[$this->name])
				&& !$this->isMarriedEmpty($scope)
			) {
				$this->raw = $scope[$this->name];
				$this->imported = true;
				
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
					$this->value = $time;
					
					return true;
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
					array($this->getName() => $value->toFullString())
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