<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
	final class PrimitiveRange extends ComplexPrimitive
	{
		const MIN	= 'min';
		const MAX	= 'max';

		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveRange
		**/
		public function setValue(/* BaseRange */ $range)
		{
			Assert::isTrue(
				$range instanceof BaseRange,
				'only ranges accepted today'
			);
			
			$this->value = $range;
			
			return $this;
		}
		
		public function getMax()
		{
			if ($this->value)
				return $this->value->getMax();
			
			return null;
		}
		
		public function getMin()
		{
			if ($this->value)
				return $this->value->getMin();
			
			return null;
		}

		public function getActualMax()
		{
			if ($range = $this->getActualValue())
				return $range->getMax();
			
			return null;
		}
		
		public function getActualMin()
		{
			if ($range = $this->getActualValue())
				return $range->getMin();
			
			return null;
		}
		
		public function importSingle($scope)
		{
			if (!BasePrimitive::import($scope) || is_array($scope[$this->name]))
				return null;
			
			if (isset($scope[$this->name]) && is_string($scope[$this->name])) {
				$array = explode('-', $scope[$this->name], 2);
				
				$range =
					BaseRange::lazyCreate(
						ArrayUtils::getArrayVar($array, 0),
						ArrayUtils::getArrayVar($array, 1)
					);
				
				if (
					$range
					&& $this->checkLimits($range)
				) {
					$this->value = $range;
					
					return true;
				}
			}
			
			return false;
		}

		public function importMarried($scope) // ;-)
		{
			if (
				($this->safeGet($scope, $this->name, self::MIN) === null)
				&& ($this->safeGet($scope, $this->name, self::MAX) === null)
			)
				return null;
			
			$range =
				BaseRange::lazyCreate(
					$this->safeGet($scope, $this->name, self::MIN),
					$this->safeGet($scope, $this->name, self::MAX)
				);
			
			if (
				$range
				&& $this->checkLimits($range)
			) {
				$this->value = $range;
				$this->raw = $scope[$this->name];
				
				return $this->imported = true;
			}
			
			return false;
		}
		
		private function checkLimits(BaseRange $range)
		{
			if (
				!(
					($this->min && $range->getMin())
					&& $range->getMin() < $this->min
				) &&
				!(
					($this->max && $range->getMax())
					&& $range->getMax() > $this->max
				)
			) {
				return true;
			}
			
			return false;
		}
		
		private function safeGet($scope, $firstDimension, $secondDimension)
		{
			if (isset($scope[$firstDimension]) && is_array($scope[$firstDimension])) {
				if (
					!empty($scope[$firstDimension][$secondDimension])
					&& is_array($scope[$firstDimension])
				) {
					return $scope[$firstDimension][$secondDimension];
				}
			}
			
			return null;
		}
	}
