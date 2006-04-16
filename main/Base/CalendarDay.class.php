<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Calendar day representation.
	 *
	 * @ingroup Helpers
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