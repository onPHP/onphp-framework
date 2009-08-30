<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Calendar day representation.
	 * 
	 * @ingroup Calendar
	**/
	class CalendarDay extends Timestamp
	{
		private $selected	= null;
		private $outside	= null;
		
		public static function create($timestamp)
		{
			return new CalendarDay($timestamp);
		}
		
		public function isSelected()
		{
			return $this->selected === true;
		}
		
		public function setSelected($selected)
		{
			$this->selected = $selected === true;
		}
		
		public function isOutside()
		{
			return $this->outside;
		}
		
		public function setOutside($outside)
		{
			$this->outside = $outside === true;
		}
	}
?>