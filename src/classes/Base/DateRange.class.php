<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Anton Lebedevich                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class DateRange
	{
		private $start	= null;
		private $end	= null;

		public function setStart(Timestamp $start)
		{
			if ($this->end && $this->end->toStamp() < $start->toStamp())
				throw new WrongArgumentException(
					'start must be lower than end'
				);

			$this->start = $start;
			return $this;
		}

		public function dropStart()
		{
			$this->start = null;
			return $this;
		}

		public function getStart()
		{
			return $this->start;
		}

		public function getEnd()
		{
			return $this->end;
		}

		public function dropEnd()
		{
			$this->end = null;
			return $this;
		}

		public function setEnd(Timestamp $end)
		{
			if ($this->start && $this->start->toStamp() > $end->toStamp())
				throw new WrongArgumentException(
					'end must be higher than start'
				);

			$this->end = $end;
			return $this;
		}
	}
?>