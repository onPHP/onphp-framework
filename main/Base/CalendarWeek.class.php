<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Calendar week representation
	 *
	 * @ingroup Base
	**/
	class CalendarWeek
	{
		// TODO: quite empty class, consider replacement or pull up all methods
		private $days = array();
		
		
		public static function create()
		{
			return new CalendarWeek();
		}
		
		public function getDays()
		{
			return $this->days;
		}
		
		public function addDay(CalendarDay $day)
		{
			$this->days[$day->toDate()] = $day;
		}
	}
?>