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

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	class PrimitiveDate extends ComplexPrimitive
	{
		const DAY		= 'day';
		const MONTH		= 'month';
		const YEAR		= 'year';

		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveDate
		**/
		public function setValue(/* Date */ $object)
		{
			$this->checkType($object);

			$this->value = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveDate
		**/
		public function setMin(/* Date */ $object)
		{
			$this->checkType($object);

			$this->min = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveDate
		**/
		public function setMax(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->max = $object;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveDate
		**/
		public function setDefault(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->default = $object;
			
			return $this;
		}
		
		public function importSingle($scope)
		{
			if (
				BasePrimitive::import($scope)
				&& (
					is_string($scope[$this->name])
					|| is_numeric($scope[$this->name])
				)
			) {
				try {
					$class = $this->getObjectName();
					$ts = new $class($scope[$this->name]);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if ($this->checkRanges($ts)) {
					$this->value = $ts;
					return true;
				}
			} elseif ($this->isEmpty($scope)) {
				return null;
			}
			
			return false;
		}

		public function isEmpty($scope)
		{
			if (
				$this->getState()->isFalse()
				|| $this->getState()->isNull()
			) {
				return empty($scope[$this->name][self::DAY])
					&& empty($scope[$this->name][self::MONTH])
					&& empty($scope[$this->name][self::YEAR]);
			} else
				return empty($scope[$this->name]);
		}
		
		public function importMarried($scope)
		{
			if (
				BasePrimitive::import($scope)
				&& isset(
					$scope[$this->name][self::DAY],
					$scope[$this->name][self::MONTH],
					$scope[$this->name][self::YEAR]
				)
				&& is_array($scope[$this->name])
			) {
				if ($this->isEmpty($scope))
					return !$this->isRequired();

				$year = (int) $scope[$this->name][self::YEAR];
				$month = (int) $scope[$this->name][self::MONTH];
				$day = (int) $scope[$this->name][self::DAY];
				
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
			return '\Onphp\Date';
		}
		
		/* void */ protected function checkType($object)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($object, $this->getObjectName())
			);
		}
	}
?>