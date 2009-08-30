<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Utilities for playing with dates and time
	 * 
	 * @ingroup Utils
	**/
	final class DateUtils extends StaticFactory
	{
		public static function getAgeByBirthDate(
			Date $birthDate, /* Date*/ $actualDate = null
		)
		{
			if ($actualDate)
				Assert::isInstance($actualDate, 'Date');
			else
				$actualDate = Date::makeToday();
			
			$result = $actualDate->getYear() - $birthDate->getYear();
			
			if (
				$actualDate->getMonth() < $birthDate->getMonth()
				|| (
					$actualDate->getMonth() == $birthDate->getMonth()
					&& $actualDate->getDay() < $birthDate->getDay()
				)
			) {
					// - Happy birthday?
					// - Happy go to hell. Not yet in this year.
					--$result;
			}
			
			return $result;
		}
	}
?>