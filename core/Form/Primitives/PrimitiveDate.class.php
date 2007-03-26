<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
		
		public function importSingle($scope)
		{
			if (
				isset($scope[$this->name])
				&& is_string($scope[$this->name])
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
			}
			
			return false;
		}

		public function isEmpty($scope)
		{
			if ($this->getState()->isFalse()) {
				return empty($scope[$this->name][self::DAY])
					|| empty($scope[$this->name][self::MONTH])
					|| empty($scope[$this->name][self::YEAR]);
			} else 
				return empty($scope[$this->name]);
		}
		
		public function importMarried($scope)
		{
			if (
				isset(
					$scope[$this->name][self::DAY], 
					$scope[$this->name][self::MONTH], 
					$scope[$this->name][self::YEAR]
				)
				&& is_array($scope[$this->name])
				&& !$this->isEmpty($scope)
			) {
				try {
					$date = new Date(
						(int) $scope[$this->name][self::YEAR].'-'
						.(int) $scope[$this->name][self::MONTH].'-'
						.(int) $scope[$this->name][self::DAY].' '
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
				$this->checkType($value);
			else
				return parent::importValue(null);
			
			return
				$this->importSingle(
					array($this->getName() => $value->toString())
				);
		}
		
		protected function checkRanges(Date $date)
		{
			return
				!($this->min && $this->min->toStamp() < $date->toStamp())
				&& !($this->max && $this->max->toStamp() > $date->toStamp());
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