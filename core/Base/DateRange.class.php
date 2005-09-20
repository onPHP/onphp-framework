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
		protected $start	= null;
		protected $end		= null;
		
		public static function create()
		{
			return new DateRange();
		}

		public function __clone()
		{
			if ($this->start)
				$this->start = clone $this->start;

			if ($this->end)
				$this->end = clone $this->end;
		}
		
		public function setStart(Timestamp $start)
		{
			if ($this->end && $this->end->toStamp() < $start->toStamp())
				throw new WrongArgumentException(
					'start must be lower than end'
				);

			$this->start = $start;
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
		
		public function lazySet($start = null, $end = null)
		{
			if ($start instanceof Timestamp && $end instanceof Timestamp) {
				if ($start->toStamp() > $end->toStamp())
					$this->setEnd($start)->setStart($end);
				else
					$this->setStart($start)->setEnd($end);
			} elseif ($start instanceof Timestamp)
				$this->setStart($start);
			elseif ($end instanceof Timestamp)
				$this->setEnd($end);
			
			return $this;
		}

		public function dropStart()
		{
			$this->start = null;
			return $this;
		}

		public function dropEnd()
		{
			$this->end = null;
			return $this;
		}
		
		public function isEmpty()
		{
			return
				($this->start === null)
				&& ($this->end === null);
		}

		public function getStart()
		{
			return $this->start;
		}

		public function getEnd()
		{
			return $this->end;
		}

		public function toDateString()
		{
			if ($this->start && $this->end)
				return "{$this->start->toDate()} - {$this->end->toDate()}";
			elseif ($this->start)
				return $this->start->toDate();
			elseif ($this->end)
				return $this->end->toDate();
			else
				return null;
		}
		
		public function toString()
		{
			if ($this->start && $this->end)
				return "{$this->start->toString()} - {$this->end->toString()}";
			elseif ($this->start)
				return $this->start->toString();
			elseif ($this->end)
				return $this->end->toString();
			else
				return null;
		}
	}
?>
