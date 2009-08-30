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
			Assert::isTrue(
				$range instanceof Range,
				'only ranges accepted today'
			);

			$this->value = $range;

			return $this;
		}

		public function importSingle(&$scope)
		{
			if (!BasePrimitive::import($scope) || is_array($scope[$this->name]))
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

			$range =
				Range::buildObject(
					$this->safeGet($scope, $name, self::MIN),
					$this->safeGet($scope, $name, self::MAX),
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

			return false;
		}

		private function safeGet(&$scope, $firstDimension, $secondDimension)
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
