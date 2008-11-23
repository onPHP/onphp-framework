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
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveRange extends ComplexPrimitive
	{
		const MIN	= 'min';
		const MAX	= 'max';

		// to be E_STRICT compatible
		public function setValue(/* Range */ $range)
		{
			Assert::isTrue(
				$range instanceof Range,
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
				$arr = explode('-', $scope[$this->name], 2);

				$range =
					Range::lazyCreate(
						ArrayUtils::getArrayVar($arr, 0),
						ArrayUtils::getArrayVar($arr, 1)
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
			$name = &$this->name;

			if (
				($this->safeGet($scope, $name, self::MIN) === null)
				&& ($this->safeGet($scope, $name, self::MAX) === null)
			)
				return null;

			$range =
				Range::lazyCreate(
					$this->safeGet($scope, $name, self::MIN),
					$this->safeGet($scope, $name, self::MAX)
				);

			if (
				$range
				&& $this->checkLimits($range)
			) {
				$this->value = $range;

				return true;
			}

			return false;
		}
		
		private function checkLimits(Range $range)
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
					isset($scope[$firstDimension][$secondDimension])
					&& is_array($scope[$firstDimension])
					&& !empty($scope[$firstDimension][$secondDimension])
				) {
					return $scope[$firstDimension][$secondDimension];
				}
			}
			
			return null;
		}
	}
?>