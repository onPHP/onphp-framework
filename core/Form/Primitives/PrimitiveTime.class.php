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
		
		public function importSingle($scope, $prefix = null)
		{
			if (!BasePrimitive::import($scope, $prefix))
				return null;
			
			$name = $this->getActualName($prefix);
			
			try {
				$time = new Time($scope[$name]);
			} catch (WrongArgumentException $e) {
				return false;
			}
				
			if ($this->checkLimits($time)) {
				$this->value = $time;
				
				return true;
			}
			
			return false;
		}
		
		public function isEmpty($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if ($this->getState()->isFalse())
				return $this->isMarriedEmpty($scope, $prefix);
			
			return empty($scope[$name]);
		}
		
		public function importMarried($scope, $prefix = null)
		{
			if (!$this->isMarriedEmpty($scope, $prefix)) {
				$name = $this->getActualName($prefix);
				
				$this->raw = $scope[$name];
				$this->imported = true;
				
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$name][self::HOURS]))
					$hours = (int) $scope[$name][self::HOURS];
				
				if (isset($scope[$name][self::MINUTES]))
					$minutes = (int) $scope[$name][self::MINUTES];
				
				if (isset($scope[$name][self::SECONDS]))
					$seconds = (int) $scope[$name][self::SECONDS];
				
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
		
		public function import($scope, $prefix = null)
		{
			if ($this->isEmpty($scope)) {
				$this->value = null;
				$this->raw = null;
				return null;
			}
			
			return parent::import($scope, $prefix);
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
		
		private function isMarriedEmpty($scope, $prefix)
		{
			$name = $this->getActualName($prefix);
			
			return empty($scope[$name][self::HOURS])
				|| empty($scope[$name][self::MINUTES])
				|| empty($scope[$name][self::SECONDS]);
		}
		
		private function checkLimits(Time $time)
		{
			return
				!($this->min && $this->min->toSeconds() > $time->toSeconds())
				&& !($this->max && $this->max->toSeconds() < $time->toSeconds());
		}
	}
?>