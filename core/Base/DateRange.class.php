<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Anton E. Lebedevich                        *
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
	 * Date's interval implementation and accompanying utility methods.
	 *
	 * @see Timestamp
	 * 
	 * @ingroup Base
	**/
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
				if ($start->toStamp() >= $end->toStamp())
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

			$left = $this->getStartStamp();
			$right = $this->getEndStamp();
			$min = $range->getStartStamp();
			$max = $range->getEndStamp();

			return (
				($min && $max 
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
				|| ($min && !$max
					&& (
						( 
							$right
							&& $min <= $right
						)
						|| !$right
					)
				)
				|| (!$min && $max
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
			$start = $this->getStartStamp();
			$end = $this->getEndStamp();
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
			Assert::isFalse(
				$this->isOpen(),
				"open range can't be splitted"
			);
			$timestamps = array();

			$start = new Timestamp($this->start->getDayStartStamp());

			$end = new Timestamp($this->end->getDayEndStamp());

			for (
				$current = $start; 
				$current->toStamp() < $end->toStamp();
				$current->modify('+1 day')
			)
				$timestamps[] = new Timestamp($current->getDayStartStamp());

			return $timestamps;
		}
		
		public static function merge($array /* of DateRanges */)
		{
			$out = array();
			foreach ($array as $range) {
				$accepted = false;
				foreach ($out as $outRange)
					if ($outRange->isNeighbour($range)) {
						$outRange->enlarge($range);
						$accepted = true;
					}
					
				if (!$accepted)
					$out[] = clone $range;
			}
			
			return $out;
		}
		
		public function isNeighbour(DateRange $range)
		{
			Assert::isTrue(! $this->isOpen() && ! $range->isOpen());
			if (
				$this->overlaps($range)
				|| $this->start->spawn('-1 day')->getDayStartStamp() 
					== $range->end->getDayStartStamp()
				|| $this->end->spawn('+1 day')->getDayStartStamp()
					== $range->start->getDayStartStamp()
			)
				return true;
			else			
				return false;
		}
		
		public function isOpen()
		{
			return !$this->start || !$this->end;
		}

		/**
		 * enlarges $this by given $range, if last one is wider
		**/
		public function enlarge(DateRange $range)
		{
			if (!$range->start)
				$this->start = null;
			elseif (
				$this->start 
				&& $this->start->toStamp() > $range->start->toStamp()
			)
				$this->start = clone $range->start;

			if (!$range->end)
				$this->end = null;
			elseif (
				$this->end
				&& $this->end->toStamp() < $range->end->toStamp()
			)
				$this->end = clone $range->end;

			return $this;
		}

		/**
		 * intersection of $this and given $range
		**/
		public function clip(DateRange $range)
		{
			Assert::isTrue($this->overlaps($range));

			if ($range->start 
				&& (
					$this->start 
					&& $range->start->toStamp() > $this->start->toStamp()
					|| !$this->start
				)
			)
				$this->start = clone $range->start;

			if ($range->end 
				&& (
					$this->end 
					&& $range->end->toStamp() < $this->end->toStamp()
					|| !$this->end
				)

			)
				$this->end = clone $range->end;

			return $this;
		}

		public function getStartStamp() // null if start is null
		{
			if ($this->start)
				return $this->start->getDayStartStamp();
			else
				return null;
		}

		public function getEndStamp() // null if end is null
		{
			if ($this->end)
				return $this->end->getDayEndStamp();
			else 
				return null;
		}

		public static function compare(DateRange $left, DateRange $right)
		{
			if ($left->isEmpty() && $right->isEmpty())
				return 0;
			elseif ($left->isEmpty())
				return 1;
			elseif ($right->isEmpty())
				return -1;

			$leftStart = $left->getStartStamp();
			$leftEnd = $left->getEndStamp();

			$rightStart = $right->getStartStamp();
			$rightEnd = $right->getEndStamp();

			if (
				!$leftStart && !$rightStart 
				|| $leftStart && $rightStart && ($leftStart == $rightStart)
			) {

				if (
					!$leftEnd && !$rightEnd
					|| $leftEnd && $rightEnd && ($leftEnd == $rightEnd)
				)
					return 0;
				elseif (!$leftEnd && $rightEnd)
					return 1;
				elseif($leftEnd && !$rightEnd)
					return -1;
				elseif ($leftEnd < $rightEnd)
					return -1;
				else
					return 1;

			} elseif (!$leftStart && $rightStart) 
				return -1;
			elseif ($leftStart && !$rightStart) 
				return 1;
			elseif ($leftStart < $rightStart)
				return -1;
			else
				return 1;
		}
		
		public function isOneDay()
		{
			return (!$this->isOpen())
				&& ($this->start->toDate() == $this->end->toDate()); 
		}
	}
?>