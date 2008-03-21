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
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveTimestamp extends PrimitiveDate
	{
		const HOURS		= 'hrs';
		const MINUTES	= 'min';
		const SECONDS	= 'sec';
		
		public function importMarried($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (
				BasePrimitive::import($scope, $prefix)
				&& isset(
					$scope[$name][self::DAY],
					$scope[$name][self::MONTH],
					$scope[$name][self::YEAR]
				)
				&& is_array($scope[$name])
			) {
				if ($this->isEmpty($scope, $prefix))
					return !$this->isRequired();
				
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$name][self::HOURS]))
					$hours = (int) $scope[$name][self::HOURS];
				
				if (isset($scope[$name][self::MINUTES]))
					$minutes = (int) $scope[$name][self::MINUTES];
				
				if (isset($scope[$name][self::SECONDS]))
					$seconds = (int) $scope[$name][self::SECONDS];
				
				$year = (int) $scope[$name][self::YEAR];
				$month = (int) $scope[$name][self::MONTH];
				$day = (int) $scope[$name][self::DAY];
				
				if (!checkdate($month, $day, $year))
					return false;
				
				try {
					$stamp = new Timestamp(
						$year.'-'.$month.'-'.$day.' '
						.$hours.':'.$minutes.':'.$seconds
					);
				} catch (WrongArgumentException $e) {
					// fsck wrong stamps
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
			return 'Timestamp';
		}
	}
?>