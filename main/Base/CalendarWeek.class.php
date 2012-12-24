<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Calendar week representation.
	 * 
	 * @ingroup Calendar
	**/
	final class CalendarWeek
	{
		private $days = array();
		
		/**
		 * @return CalendarWeek
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getDays()
		{
			return $this->days;
		}
		
		/**
		 * @return CalendarWeek
		**/
		public function addDay(CalendarDay $day)
		{
			$this->days[$day->toDate()] = $day;
			
			return $this;
		}
	}
