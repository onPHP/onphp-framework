<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	final class PrimitiveTimestamp extends PrimitiveDate
	{
		const HOURS		= 'hrs';
		const MINUTES	= 'min';
		const SECONDS	= 'sec';
		
		public function importMarried($scope)
		{
			if (
				!$this->isEmpty($scope)
			) {
				$hours = $minutes = $seconds = 0;
				
				if (isset($scope[$this->name][self::HOURS]))
					$hours = (int) $scope[$this->name][self::HOURS];

				if (isset($scope[$this->name][self::MINUTES]))
					$minutes = (int) $scope[$this->name][self::MINUTES];

				if (isset($scope[$this->name][self::SECONDS]))
					$seconds = (int) $scope[$this->name][self::SECONDS];
				
				try {
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
		
		protected function checkType($object)
		{
			return ($object instanceof Timestamp);
		}
	}
?>