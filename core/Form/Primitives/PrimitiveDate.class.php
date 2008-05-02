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
		
		public function getTypeName()
		{
			return 'Date';
		}
		
		public function isObjectType()
		{
			return true;
		}
		
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
			
			$this->atom->setMin($object);
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDate
		**/
		public function setMax(/* Date */ $object)
		{
			$this->checkType($object);
			
			$this->atom->setMax($object);
			
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
		
		public function importSingle(array $scope)
		{
			return RangedPrimitive::import($scope);
		}
		
		public function importMarried(array $scope)
		{
			if (
				BasePrimitive::import($scope)
				&& is_array($scope[$this->name])
				&& isset(
					$scope[$this->name][self::DAY],
					$scope[$this->name][self::MONTH],
					$scope[$this->name][self::YEAR]
				)
			) {
				if ($this->isEmpty($scope))
					return !$this->isRequired();
				
				$year = (int) $scope[$this->name][self::YEAR];
				$month = (int) $scope[$this->name][self::MONTH];
				$day = (int) $scope[$this->name][self::DAY];
				
				if (!checkdate($month, $day, $year))
					return false;
				
				$scope[$this->name] = $year.'-'.$month.'-'.$day;
				
				return RangedPrimitive::import($scope);
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
				array(
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
		
		/* void */ protected function checkType($object)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($object, $this->getTypeName())
			);
		}
		
		protected function isEmpty($scope)
		{
			if ($this->getState()->isFalse()) {
				return empty($scope[$this->name][self::DAY])
					&& empty($scope[$this->name][self::MONTH])
					&& empty($scope[$this->name][self::YEAR]);
			} else
				return empty($scope[$this->name]);
		}
	}
?>