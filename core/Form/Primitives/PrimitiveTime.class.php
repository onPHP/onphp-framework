<?php
/****************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                      *
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
		
		public function getTypeName()
		{
			return 'Time';
		}
		
		public function isObjectType()
		{
			return true;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setValue(/* Time */ $time)
		{
			Assert::isInstance($time, 'Time');
			
			$this->value = $time;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setMin(/* Time */ $time)
		{
			Assert::isInstance($time, 'Time');
			
			$this->atom->setMin($time);
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setMax(/* Time */ $time)
		{
			Assert::isInstance($time, 'Time');
			
			$this->atom->setMax($time);
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveTime
		**/
		public function setDefault(/* Time */ $time)
		{
			Assert::isInstance($time, 'Time');
			
			$this->default = $time;
			
			return $this;
		}
		
		public function importSingle(array $scope)
		{
			return RangedPrimitive::import($scope);
		}
		
		public function importMarried(array $scope)
		{
			if (!$this->isMarriedEmpty($scope)) {
				$this->raw = $scope[$this->name];
				$this->imported = true;
				
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$this->name][self::HOURS]))
					$hours = (int) $scope[$this->name][self::HOURS];
				
				if (isset($scope[$this->name][self::MINUTES]))
					$minutes = (int) $scope[$this->name][self::MINUTES];
				
				if (isset($scope[$this->name][self::SECONDS]))
					$seconds = (int) $scope[$this->name][self::SECONDS];
				
				$scope[$this->name] = $hours.':'.$minutes.':'.$seconds;
				
				return RangedPrimitive::import($scope);
			}
			
			return false;
		}
		
		public function importValue($value)
		{
			if ($value)
				Assert::isTrue($value instanceof Time);
			else
				$value = null;
			
			return
				RangedPrimitive::import(
					array($this->getName() => $value->toString())
				);
		}
		
		public function isEmpty($scope)
		{
			if ($this->getState()->isFalse())
				return $this->isMarriedEmpty($scope);
			
			return empty($scope[$this->name]);
		}
		
		private function isMarriedEmpty($scope)
		{
			return empty($scope[$this->name][self::HOURS])
				|| empty($scope[$this->name][self::MINUTES])
				|| empty($scope[$this->name][self::SECONDS]);
		}
	}
?>