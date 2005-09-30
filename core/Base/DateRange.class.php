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

		public function toDateString($delimiter = '-')
		{
			if ($this->start && $this->end)
				return
					"{$this->start->toDate($delimiter)} - "
					."{$this->end->toDate($delimiter)}";
			elseif ($this->start)
				return $this->start->toDate($delimiter);
			elseif ($this->end)
				return $this->end->toDate($delimiter);
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

		public function overlaps(DateRange $range)
		{
			if ($this->isEmpty() || $range->isEmpty())
				return true;

			if ($this->start)
				$left = mktime(
					0,0,0,
					$this->start->getMonth(),
					$this->start->getDay(),
					$this->start->getYear()
				);
			else
				$left = null;

			if ($this->end)
				$right = mktime(
					23,59,59,
					$this->end->getMonth(),
					$this->end->getDay(),
					$this->end->getYear()
				);
			else
				$right = null;

			if ($range->start)
				$min = mktime(
					0,0,0,
					$range->start->getMonth(),
					$range->start->getDay(),
					$range->start->getYear()
				);
			else
				$min = null;

			if ($range->end)
				$max = mktime(
					23,59,59,
					$range->end->getMonth(),
					$range->end->getDay(),
					$range->end->getYear()
				);
			else
				$max = null;

			return (
				( $min && $max 
					&& (
						( 
							$left 
							&& $right
							&& (
								$left <= $min && $min <= $right
								|| $min <= $left && $left <= $max
							)
						)
						|| ( 
							!$left
							&& $min <= $right
						)
						|| ( 
							!$right 
							&& $left <= $max
						)
					)
				)
				|| ( $min && !$max
					&& (
						( 
							$right
							&& $min <= $right
						)
						|| !$right
					)
				)
				|| ( !$min && $max
					&& (
						( 
							$left 
							&& $left <= $max
						)
						|| !$left
					)
				)
			);
		} 

		public function contains(Timestamp $date)
		{
			if ($this->start)
				$start = mktime(
					0,0,0,
					$this->start->getMonth(),
					$this->start->getDay(),
					$this->start->getYear()
				);
			else
				$start = null;

			if ($this->end)
				$end = mktime(
					23,59,59,
					$this->end->getMonth(),
					$this->end->getDay(),
					$this->end->getYear()
				);
			else 
				$end = null;

			$probe = $date->toStamp();

			if (
				(!$start && !$end)
				|| (!$start && $end >= $probe)
				|| (!$end && $start <= $probe)
				|| ($start <= $probe && $end >= $probe)
			)
				return true;
			else 
				return false;
		}

		public function split()
		{
			Assert::isTrue(
				$this->start && $this->end, 
				"open range can't be splitted"
			);
			$timestamps = array();

			$start = new Timestamp(
				mktime(
					0,0,0,
					$this->start->getMonth(),
					$this->start->getDay(),
					$this->start->getYear()
				)
			);

			$end = new Timestamp(
				mktime(
					23,59,59,
					$this->end->getMonth(),
					$this->end->getDay(),
					$this->end->getYear()
				)
			);

			for (
				$current = $start; 
				$current->toStamp() < $end->toStamp();
				$current->modify('+1 day')
			)
				$timestamps[] = new Timestamp(
					mktime(
						0,0,0,
						$current->getMonth(),
						$current->getDay(),
						$current->getYear()
					)
				);

			return $timestamps;
		}
	}
?>
