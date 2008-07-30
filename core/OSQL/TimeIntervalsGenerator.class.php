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
			if (!defined('__I_HATE_MY_KARMA__'))
				throw new UnsupportedMethodException(
					'do not use it. please.'
				);
			
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
		
		public function toDialectString(Dialect $dialect)
		{
			if (!$this->range || !$this->interval)
				throw new WrongStateException(
					'define time range and interval units first'
				);
			
			// FIXME
			if (!$dialect instanceof PostgresDialect)
				throw new UnimplementedFeatureException(
					'only tested with postgres'
				);
			
			$firstIntervalStart =
				$this->interval->truncate(
					$this->range->getStart(), !$this->overlapped
				);
				
			$maxIntervals =
				$this->interval->countInRange(
					$this->range, $this->overlapped
				) - 1;
			
			// FIXME: use OSQL
			$result = "SELECT "
				."'{$firstIntervalStart->toString()}'::timestamp "
				."+ '1 {$this->interval->getName()}'::interval * i "
				."AS ".$dialect->quoteField($this->field)." "
				."FROM generate_series(0, {$maxIntervals}) AS i";
			
			return $result;
		}
	}
?>
