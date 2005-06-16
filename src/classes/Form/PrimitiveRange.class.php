<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PrimitiveRange extends ComplexPrimitive
	{
		const MIN	= 'min';
		const MAX	= 'max';
		
		private $swapAllowed = true;
		
		public function setSwapAllowed()
		{
			$this->swapAllowed = true;

			return $this;
		}
		
		public function setSwapDisallowed()
		{
			$this->swapAllowed = false;
			
			return $this;
		}
		
		// to be E_STRICT compatible
		public function setValue(/* Range */ $range)
		{
			if (!$range instanceof Range)
				throw new WrongArgumentException(
					'only ranges accepted today'
				);
			
			$this->value = $range;
			
			return $this;
		}

		public function importSingle(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if (isset($scope[$this->name]) && is_string($scope[$this->name])) {
				$arr = explode('-', $scope[$this->name], 2);
				
				$range =
					Range::buildObject(
						ArrayUtils::getArrayVar($arr, 0),
						ArrayUtils::getArrayVar($arr, 1),
						$this->swapAllowed
					);

				if ($range &&
					!(
						($this->min && $range->getMin()) &&
						$range->getMin() < $this->min
					) && 
					!(
						($this->max && $range->getMax()) &&
						$range->getMax() > $this->max
					)
				) {
					$this->value = $range;
					
					return true;
				}
			}
			
			return false;
		}
		
		public function importMarried(&$scope) // ;-)
		{
			$name = &$this->name;

			if (
				($this->safeGet($scope, $name, self::MIN) === null) &&
				($this->safeGet($scope, $name, self::MAX) === null)
			)
				return null;
				
			if ($range =
				Range::buildObject(
					$this->safeGet($scope, $name, self::MIN),
					$this->safeGet($scope, $name, self::MAX),
					$this->swapAllowed
				)
			) {
				$this->value = $range;

				return true;
			}
			
			return false;
		}
		
		private function safeGet(&$scope, $firstDimension, $secondDimension)
		{
			if (isset($scope[$firstDimension]) && is_array($scope[$firstDimension])) {
				if (isset($scope[$firstDimension][$secondDimension]) &&
					!empty($scope[$firstDimension][$secondDimension])) {
					return $scope[$firstDimension][$secondDimension];						
				}
			}
			
			return null;
		}
	}
?>