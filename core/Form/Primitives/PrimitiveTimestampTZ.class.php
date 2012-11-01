<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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

	final class PrimitiveTimestampTZ extends PrimitiveTimestamp
	{
		const ZONE = 'zone';

		public function importMarried($scope)
		{
			if (
				BasePrimitive::import($scope)
				&& isset(
					$scope[$this->name][self::DAY],
					$scope[$this->name][self::MONTH],
					$scope[$this->name][self::YEAR],
					$scope[$this->name][self::HOURS],
					$scope[$this->name][self::MINUTES],
					$scope[$this->name][self::SECONDS],
					$scope[$this->name][self::ZONE]
				)
				&& is_array($scope[$this->name])
			) {
				if ($this->isEmpty($scope))
					return !$this->isRequired();

				$zone = $scope[$this->name][self::ZONE];

				$hours = (int) $scope[$this->name][self::HOURS];
				$minutes = (int) $scope[$this->name][self::MINUTES];
				$seconds = (int) $scope[$this->name][self::SECONDS];

				$year = (int) $scope[$this->name][self::YEAR];
				$month = (int) $scope[$this->name][self::MONTH];
				$day = (int) $scope[$this->name][self::DAY];

				if (!checkdate($month, $day, $year))
					return false;

				try {
					$stamp = new TimestampTZ(
						$year.'-'.$month.'-'.$day.' '
						.$hours.':'.$minutes.':'.$seconds
						.' '.$zone
					);
				} catch (WrongArgumentException $e) {
					return false;
				}

				if ($this->checkRanges($stamp)) {
					$this->value = $stamp;
					return true;
				}
			}

			return false;
		}

		protected function getObjectName()
		{
			return '\Onphp\TimestampTZ';
		}

		public function exportValue()
		{
			$parent = parent::exportValue();

			if(is_array($parent)) {
				if($this->value) {
					$parent[static::ZONE] = $this->value->getDateTime()->getTimezone()->getName();

				} else {
					$parent[static::ZONE] = null;
				}

			}

			return $parent;
		}
	}
?>