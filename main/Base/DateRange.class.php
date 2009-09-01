<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Date's interval implementation and accompanying utility methods.
	 * 
	 * @see Date
	 * @see TimestampRange
	 * 
	 * @ingroup Helpers
	**/
	class DateRange implements Stringable, SingleRange
	{
		private $start	= null;
		private $end	= null;
		
		private $dayStartStamp	= null;
		private $dayEndStamp	= null;
		
		/**
		 * @return DateRange
		**/
		public static function create($start = null, $end = null)
		{
			return new self($start, $end);
		}
		
		public function __construct($start = null, $end = null)
		{
			if ($start)
				$this->setStart($start);
			
			if ($end)
				$this->setEnd($end);
		}
		
		public function __clone()
		{
			if ($this->start)
				$this->start = clone $this->start;
			
			if ($this->end)
				$this->end = clone $this->end;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return DateRange
		**/
		public function setStart(/* Date */ $start)
		{
			$this->checkType($start);
			
			if ($this->end && $this->end->toStamp() < $start->toStamp())
				throw new WrongArgumentException(
					'start must be lower than end'
				);
			
			$this->start = $start;
			$this->dayStartStamp = null;
			
			return $this;
		}
		
		/**
		 * @return DateRange
		**/
		public function safeSetStart(/* Date */ $start)
		{
			if (
				!$this->getEnd()
				|| Timestamp::compare(
					$start, $this->getEnd()
				) < 0
			)
				$this->setStart($start);
			
			elseif ($this->getEnd())
				$this->setStart($this->getEnd());
			
			return $this;
		}
		
		/**
		 * @return DateRange
		**/
		public function safeSetEnd(/* Date */ $end)
		{
			if (
				!$this->getStart()
				|| Timestamp::compare(
					$end, $this->getStart()
				) > 0
			)
				$this->setEnd($end);
			
			elseif ($this->getStart())
				$this->setEnd($this->getStart());
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return DateRange
		**/
		public function setEnd(/* Date */ $end)
		{
			$this->checkType($end);
			
			if ($this->start && $this->start->toStamp() > $end->toStamp())
				throw new WrongArgumentException(
					'end must be higher than start'
				);
			
			$this->end = $end;
			$this->dayEndStamp = null;
			return $this;
		}
		
		/**
		 * @return DateRange
		**/
		public function lazySet($start = null, $end = null)
		{
			if ($start)
				$this->checkType($start);
			
			if ($end)
				$this->checkType($end);
			
			if ($start && $end) {
				if ($start->toStamp() >= $end->toStamp())
					$this->setEnd($start)->setStart($end);
				else
					$this->setStart($start)->setEnd($end);
			} elseif ($start)
				$this->setStart($start);
			elseif ($end)
				$this->setEnd($end);
			
			return $this;
		}
		
		/**
		 * @return DateRange
		**/
		public function dropStart()
		{
			$this->start = null;
			$this->dayStartStamp = null;
			return $this;
		}
		
		/**
		 * @return DateRange
		**/
		public function dropEnd()
		{
			$this->end = null;
			$this->dayEndStamp = null;
			return $this;
		}
		
		public function isEmpty()
		{
			return
				($this->start === null)
				&& ($this->end === null);
		}
		
		/**
		 * @return Date
		**/
		public function getStart()
		{
			return $this->start;
		}
		
		/**
		 * @return Date
		**/
		public function getEnd()
		{
			return $this->end;
		}
		
		public function toDateString(
			$internalDelimiter = '-',
			$dateDelimiter = ' - '
		)
		{
			if ($this->start && $this->end)
				return
					"{$this->start->toDate($internalDelimiter)}"
					.$dateDelimiter
					."{$this->end->toDate($internalDelimiter)}";
			elseif ($this->start)
				return $this->start->toDate($internalDelimiter);
			elseif ($this->end)
				return $this->end->toDate($internalDelimiter);
			
			return null;
		}
		
		public function toString($delimiter = ' - ')
		{
			if ($this->start && $this->end)
				return
					$this->start->toString()
					.$delimiter
					.$this->end->toString();
			elseif ($this->start)
				return $this->start->toString();
			elseif ($this->end)
				return $this->end->toString();
			
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
				(
					$min
					&& $max
					&& (
						(
							$left
							&& $right
							&& (
								(($left <= $min) && ($min <= $right))
								|| (($min <= $left) && ($left <= $max))
							)
						) || (
							!$left
							&& ($min <= $right)
						) || (
							!$right
							&& ($left <= $max)
						)
					)
				) || (
					$min
					&& !$max
					&& (
						!$right
						|| (
							$right
							&& ($min <= $right)
						)
					)
				) || (
					!$min
					&& $max
					&& (
						!$left
						|| (
							$left
							&& ($left <= $max)
						)
					)
				)
			);
		}
		
		public function contains(/* Timestamp */ $date)
		{
			$this->checkType($date);
			
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
			
			return false;
		}

		public function split()
		{
			Assert::isFalse(
				$this->isOpen(),
				"open range can't be splitted"
			);
			
			$dates = array();
			
			$start = new Date($this->start->getDayStartStamp());
			
			$endStamp = $this->end->getDayEndStamp();
			
			for (
				$current = $start;
				$current->toStamp() < $endStamp;
				$current->modify('+1 day')
			) {
				$dates[] = new Date($current->getDayStartStamp());
			}
			
			return $dates;
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
			Assert::isTrue(!$this->isOpen() && !$range->isOpen());
			
			if (
				$this->overlaps($range)
				|| (
					$this->start->spawn('-1 day')->getDayStartStamp()
					== $range->end->getDayStartStamp()
				) || (
					$this->end->spawn('+1 day')->getDayStartStamp()
					== $range->start->getDayStartStamp()
				)
			)
				return true;
			
			return false;
		}
		
		public function isOpen()
		{
			return !$this->start || !$this->end;
		}
		
		/**
		 * enlarges $this by given $range, if last one is wider
		 * 
		 * @return DateRange
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
		 * 
		 * @return DateRange
		**/
		public function clip(DateRange $range)
		{
			Assert::isTrue($this->overlaps($range));

			if (
				$range->start
				&& (
					$this->start
					&& $range->start->toStamp() > $this->start->toStamp()
					|| !$this->start
				)
			)
				$this->start = clone $range->start;
			
			if (
				$range->end
				&& (
					$this->end
					&& $range->end->toStamp() < $this->end->toStamp()
					|| !$this->end
				)
			)
				$this->end = clone $range->end;

			return $this;
		}

		/**
		 * result is read-only, no error checking
		 * 
		 * @return DateRange
		**/
		public function lightCopyOnClip(DateRange $range)
		{
			$copy = DateRange::create();
			
			if (
				$range->start
				&& (
					$this->start
					&& $range->start->toStamp() > $this->start->toStamp()
					|| !$this->start
				)
			)
				$copy->start = $range->start;
			else
				$copy->start = $this->start;
			
			if (
				$range->end
				&& (
					$this->end
					&& $range->end->toStamp() < $this->end->toStamp()
					|| !$this->end
				)
			)
				$copy->end = $range->end;
			else
				$copy->end = $this->end;
			
			return $copy;
		}

		public function getStartStamp() // null if start is null
		{
			if ($this->start) {
				if (!$this->dayStartStamp) {
					$this->dayStartStamp = $this->start->getDayStartStamp();
				}
				
				return $this->dayStartStamp;
			}
			
			return null;
		}
		
		public function getEndStamp() // null if end is null
		{
			if ($this->end) {
				if (!$this->dayEndStamp) {
					$this->dayEndStamp = $this->end->getDayEndStamp();
				}
				
				return $this->dayEndStamp;
			}
			
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
				elseif ($leftEnd && !$rightEnd)
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
		
		/**
		 * @return TimestampRange
		**/
		public function toTimestampRange()
		{
			return
				TimestampRange::create(
					$this->getStart()->toTimestamp(),
					$this->getEnd()->toTimestamp()
				);
		}
		
		protected function checkType($value)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($value, $this->getObjectName())
			);
		}
		
		protected function getObjectName()
		{
			return 'Date';
		}
	}
?>