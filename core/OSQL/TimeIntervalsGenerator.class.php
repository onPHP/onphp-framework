<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class TimeIntervalsGenerator extends QueryIdentification
	{
		const ITERATOR_ALIAS	= 'iterator';
		
		private $range		= null;
		private $interval	= null;
		
		private $overlapped	= true;
		
		private $field		= 'time';
		
		public static function create()
		{
			return new self;
		}
		
		public function setRange(TimestampRange $range)
		{
			$this->range = $range;
			
			return $this;
		}
		
		/**
		 * @return TimestampRange
		**/
		public function getRange()
		{
			return $this->range;
		}
		
		public function setInterval(IntervalUnit $interval)
		{
			$this->interval = $interval;
			
			return $this;
		}
		
		public function setOverlapped($overlapped = true)
		{
			Assert::isBoolean($overlapped);
			
			$this->overlapped = ($overlapped === true);
			
			return $this;
		}
		
		public function isOverlapped()
		{
			return $this->overlapped;
		}
		
		public function getField()
		{
			return $this->field;
		}
		
		public function setField($field)
		{
			$this->field = $field;
			
			return $this;
		}
		
		/**
		 * @return IntervalUnit
		**/
		public function getInterval()
		{
			return $this->interval;
		}
		
		public function toSelectQuery()
		{
			if (!$this->range || !$this->interval)
				throw new WrongStateException(
					'define time range and interval units first'
				);
			
			if (!$this->range->getStart() || !$this->range->getEnd())
				throw new WrongArgumentException(
					'cannot operate with unlimited range'
				);
			
			$firstIntervalStart =
				$this->interval->truncate(
					$this->range->getStart(), !$this->overlapped
				);
				
			$maxIntervals =
				$this->interval->countInRange(
					$this->range, $this->overlapped
				) - 1;
			
			$generator = $this->getSeriesGenerator(0, $maxIntervals);
			
			$result = OSQL::select()->
				from($generator, self::ITERATOR_ALIAS)->
				get(
					Expression::add(
						DBValue::create($firstIntervalStart->toString())->
						castTo(
							DataType::create(DataType::TIMESTAMP)->
							getName()
						),
						
						Expression::mul(
							DBValue::create("1 {$this->interval->getName()}")->
							castTo(
								DataType::create(DataType::INTERVAL)->
								getName()
							),
							
							DBField::create(self::ITERATOR_ALIAS)
						)
					),
					$this->field
				);
			
			return $result;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $this->toSelectQuery()->toDialectString($dialect);
		}
		
		/**
		 * @return DialectString
		 * 
		 * FIXME: DBI-result, method works only for PostgreSQL.
		 * Research how to generate series of values in MySQL and implement
		 * this.
		**/
		private function getSeriesGenerator($start, $stop, $step = null)
		{
			if (!$step)
				$result = SQLFunction::create(
					'generate_series',
					DBValue::create($start)->
					castTo(DataType::create(DataType::INTEGER)->getName()),
					
					DBValue::create($stop)->
					castTo(DataType::create(DataType::INTEGER)->getName())
				);
			else
				$result = SQLFunction::create(
					'generate_series',
					DBValue::create($start)->
					castTo(DataType::create(DataType::INTEGER)->getName()),
					
					DBValue::create($stop)->
					castTo(DataType::create(DataType::INTEGER)->getName()),
					
					DBValue::create($step)->
					castTo(DataType::create(DataType::INTEGER)->getName())
				);
			
			return $result;
		}
	}
?>