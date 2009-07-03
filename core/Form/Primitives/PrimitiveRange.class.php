<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov                     *
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
	final class PrimitiveRange extends ComplexPrimitive
	{
		const MIN	= 'min';
		const MAX	= 'max';
		
		public function getTypeName()
		{
			return 'NumericRange';
		}
		
		public function isObjectType()
		{
			return true;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveRange
		**/
		public function setValue(/* Range */ $range)
		{
			Assert::isTrue(
				$range instanceof Range,
				'only ranges accepted today'
			);
			
			$this->value = $range;
			
			return $this;
		}
		
		public function getStart()
		{
			if ($this->value)
				return $this->value->getStart();
			
			return null;
		}
		
		public function getEnd()
		{
			if ($this->value)
				return $this->value->getEnd();
			
			return null;
		}
		
		public function importSingle(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			elseif (is_array($scope[$this->name]))
				return false;
			
			if (isset($scope[$this->name]) && is_string($scope[$this->name])) {
				$array = explode('-', $scope[$this->name], 2);
				
				$range =
					Range::lazyCreate(
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
		
		public function importMarried(array $scope) // ;-)
		{
			$min = $this->safeGet($scope, $this->name, self::MIN);
			$max = $this->safeGet($scope, $this->name, self::MAX);
			
			if ((null === $min) && (null === $max))
				return null;
			
			$range = NumericRange::lazyCreate($min, $max);
			
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
					!empty($scope[$firstDimension][$secondDimension])
					&& is_array($scope[$firstDimension])
				) {
					return $scope[$firstDimension][$secondDimension];
				}
			}
			
			return null;
		}
	}
?>