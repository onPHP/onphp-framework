<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveDate extends ComplexPrimitive
	{
		const DAY		= 'day';
		const MONTH		= 'month';
		const YEAR		= 'year';
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDate
		**/
		public function setValue(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->value = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDate
		**/
		public function setMin(/* Date */ $object)
		{
			$this->checkType($object);

			$this->min = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDate
		**/
		public function setMax(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->max = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDate
		**/
		public function setDefault(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->default = $object;
			
			return $this;
		}
		
		public function importSingle($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (
				BasePrimitive::import($scope, $prefix)
				&& (
					is_string($scope[$name])
					|| is_numeric($scope[$name])
				)
			) {
				try {
					$class = $this->getObjectName();
					$ts = new $class($scope[$name]);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if ($this->checkRanges($ts)) {
					$this->value = $ts;
					return true;
				}
			}
			
			return false;
		}
		
		public function isEmpty($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if ($this->getState()->isFalse()) {
				return empty($scope[$name][self::DAY])
					&& empty($scope[$name][self::MONTH])
					&& empty($scope[$name][self::YEAR]);
			} else
				return empty($scope[$name]);
		}
		
		public function importMarried($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (
				BasePrimitive::import($scope, $prefix)
				&& isset(
					$scope[$name][self::DAY],
					$scope[$name][self::MONTH],
					$scope[$name][self::YEAR]
				)
				&& is_array($scope[$name])
			) {
				if ($this->isEmpty($scope, $prefix))
					return !$this->isRequired();
				
				$year = (int) $scope[$name][self::YEAR];
				$month = (int) $scope[$name][self::MONTH];
				$day = (int) $scope[$name][self::DAY];
				
				if (!checkdate($month, $day, $year))
					return false;
				
				try {
					$date = new Date(
						$year.'-'.$month.'-'.$day
					);
				} catch (WrongArgumentException $e) {
					// fsck wrong dates
					return false;
				}
				
				if ($this->checkRanges($date)) {
					$this->value = $date;
					return true;
				}
			}
			
			return false;
		}
		
		public function importValue($value)
		{
			if ($value)
				$this->checkType($value);
			else
				return parent::importValue(null);
			
			$singleScope = array($this->getName() => $value->toString());
			$marriedRaw =
				array (
					self::DAY => $value->getDay(),
					self::MONTH => $value->getMonth(),
					self::YEAR => $value->getYear(),
				);
			
			if ($value instanceof Timestamp) {
				$marriedRaw[PrimitiveTimestamp::HOURS] = $value->getHour();
				$marriedRaw[PrimitiveTimestamp::MINUTES] = $value->getMinute();
				$marriedRaw[PrimitiveTimestamp::SECONDS] = $value->getSecond();
			}
			
			$marriedScope = array($this->getName() => $marriedRaw);
			
			if ($this->getState()->isTrue())
				return $this->importSingle($singleScope);
			elseif ($this->getState()->isFalse())
				return $this->importMarried($marriedScope);
			else {
				if (!$this->importMarried($marriedScope))
					return $this->importSingle($singleScope);
				
				return $this->imported = true;
			}
		}
		
		public function exportValue()
		{
			if ($this->value === null) {
				if ($this->getState()->isTrue())
					return null;
				else
					return array(
						self::DAY => null,
						self::MONTH => null,
						self::YEAR => null,
					);
			}
			
			if ($this->getState()->isTrue())
				return $this->value->toString();
			else
				return array(
					self::DAY => $this->value->getDay(),
					self::MONTH => $this->value->getMonth(),
					self::YEAR => $this->value->getYear(),
				);
		}
		
		protected function checkRanges(Date $date)
		{
			return
				(!$this->min || ($this->min->toStamp() <= $date->toStamp()))
				&& (!$this->max || ($this->max->toStamp() >= $date->toStamp()));
		}
		
		protected function getObjectName()
		{
			return 'Date';
		}
		
		/* void */ protected function checkType($object)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($object, $this->getObjectName())
			);
		}
	}
?>