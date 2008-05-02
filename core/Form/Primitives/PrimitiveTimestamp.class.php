<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
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
	final class PrimitiveTimestamp extends PrimitiveDate
	{
		const HOURS		= 'hrs';
		const MINUTES	= 'min';
		const SECONDS	= 'sec';
		
		public function getTypeName()
		{
			return 'Timestamp';
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
				
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$this->name][self::HOURS]))
					$hours = (int) $scope[$this->name][self::HOURS];
				
				if (isset($scope[$this->name][self::MINUTES]))
					$minutes = (int) $scope[$this->name][self::MINUTES];
				
				if (isset($scope[$this->name][self::SECONDS]))
					$seconds = (int) $scope[$this->name][self::SECONDS];
				
				$year = (int) $scope[$this->name][self::YEAR];
				$month = (int) $scope[$this->name][self::MONTH];
				$day = (int) $scope[$this->name][self::DAY];
				
				if (!checkdate($month, $day, $year))
					return false;
				
				$scope[$this->name] =
					$year.'-'.$month.'-'.$day.' '
					.$hours.':'.$minutes.':'.$seconds;
				
				return RangedPrimitive::import($scope);
			}
			
			return false;
		}
	}
?>