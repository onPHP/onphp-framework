<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveDate extends ComplexPrimitive
	{
		const DAY		= 'day';
		const MONTH		= 'month';
		const YEAR		= 'year';

		const HOURS		= 'hrs';
		const MINUTES	= 'min';
		const SECONDS	= 'sec';
		
		// typeHinting commented out due to stupid E_STRICT
		public function setValue(/* Timestamp */ $stamp)
		{
			Assert::isTrue($stamp instanceof Timestamp);

			$this->value = $stamp;
			
			return $this;
		}
		
		public function setMin(/* Timestamp */ $stamp)
		{
			Assert::isTrue($stamp instanceof Timestamp);

			$this->min = $stamp;
			
			return $this;
		}
		
		public function setMax(/* Timestamp */ $stamp)
		{
			Assert::isTrue($stamp instanceof Timestamp);
			
			$this->max = $stamp;
			
			return $this;
		}
		
		public function setDefault(/* Timestamp */ $stamp)
		{
			Assert::isTrue($stamp instanceof Timestamp);
			
			$this->default = $stamp;
			
			return $this;
		}
		
		public function importSingle($scope)
		{
			if (
				isset($scope[$this->name])
				&& (
					is_string($scope[$this->name])
					|| is_numeric($scope[$this->name])
				)
			) {
				try {
					$ts = new Timestamp($scope[$this->name]);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if (
					!($this->min && $this->min->toStamp() > $ts->toStamp())
					&& !($this->max && $this->max->toStamp() < $ts->toStamp())
				) {
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
				&& !empty($scope[$this->name][self::DAY])
				&& !empty($scope[$this->name][self::MONTH])
				&& !empty($scope[$this->name][self::YEAR])
			) {
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$this->name][self::HOURS]))
					$hours = (int) $scope[$this->name][self::HOURS];

				if (isset($scope[$this->name][self::MINUTES]))
					$minutes = (int) $scope[$this->name][self::MINUTES];

				if (isset($scope[$this->name][self::SECONDS]))
					$seconds = (int) $scope[$this->name][self::SECONDS];
				
				try {
					// TODO: optimize a bit this voodoo
					$stamp = new Timestamp(
						(int) $scope[$this->name][self::YEAR].'-'
						.(int) $scope[$this->name][self::MONTH].'-'
						.(int) $scope[$this->name][self::DAY].' '
						.$hours.':'.$minutes.':'.$seconds
					);
				} catch (WrongArgumentException $e) {
					// fsck wrong stamps
					return false;
				}
				
				if (
					!($this->min && $this->min->toStamp() < $stamp->toStamp())
					&& !($this->max && $this->max->toStamp() > $stamp->toStamp())
				) {
					try {
						$this->value = $stamp;
						
						return true;
					} catch (WrongArgumentException $e) {
						return false;
					}
				}
			}

			return false;
		}
		
		public function import($scope)
		{
			if ($this->isEmpty($scope))
				return null;

			return parent::import($scope);
		}
		
		public function importValue($value)
		{
			if ($value)
				Assert::isTrue($value instanceof Timestamp);
			else
				return parent::importValue(null);
			
			return
				$this->importSingle(
					array($this->getName() => $value->toString())
				);
		}
	}
?>