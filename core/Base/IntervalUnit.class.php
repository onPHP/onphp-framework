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
	 * @ingroup Base
	**/
	final class IntervalUnit
	{
		private $name		= null;
		
		private $months		= null;
		private $days		= null;
		private $seconds	= null;
		
		public static function create($name)
		{
			return self::getInstance($name);
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return Timestamp
		 *
		 * Emulates PostgreSQL's date_tunc() function
		 * 
		**/
		public function truncate(Timestamp $time, $ceil = false)
		{
			$function = $ceil ? 'ceil' : 'floor';
			
			if ($this->seconds) {
				
				if ($this->seconds < 1)
					return $time->spawn();
				
				$truncated = (int)(
					$function($time->toStamp() / $this->seconds) * $this->seconds
				);
				
				return Timestamp::create($truncated);
				
			} elseif ($this->days) {
				
				$epochStartTruncated = Date::create('1970-01-05');
				
				$difference = Date::dayDifference(
					$epochStartTruncated, Date::create($time->toDate())
				);
				
				$truncated = (int)(
					$function($difference / $this->days) * $this->days
				);
				
				return Timestamp::create(
					$epochStartTruncated->spawn("$truncated days")->toStamp()
				);
				
			} elseif ($this->months) {
				
				$monthsCount = $time->getYear() * 12 + ($time->getMonth() - 1);
				
				if (
					$ceil
					&& (
						($time->getDay() - 1) + $time->getHour()
						+ $time->getMinute() + $time->getSecond() > 0
					)
				)
					$monthsCount += 0.1; // delta
				
				$truncated = (int)(
					$function($monthsCount / $this->months) * 
						($this->months)
				);
				
				$months = $truncated % 12;
				
				$years = ($truncated - $months) / 12;
				
				Assert::isEqual($years, (int)$years);
				
				$years = (int)$years;
				
				$months = $months + 1;
				
				return Timestamp::create("$years-$months-01 00:00:00");
			}
			
			Assert::isUnreachable();
		}
		
		public function countInRange(
			TimestampRange $range,
			$overlappedBounds = true
		)
		{
			$start = $this->truncate(
				$range->getStart(), !$overlappedBounds
			);
			
			$end = $this->truncate(
				$range->getEnd(), $overlappedBounds
			);
			
			if ($this->seconds) {
				
				$result =
					($end->toStamp() - $start->toStamp())
					/ $this->seconds;
				
			} elseif ($this->days) {
				
				$epochStartTruncated = Date::create('1970-01-05');
				
				$startDifference = Date::dayDifference(
					$epochStartTruncated, Date::create($start->toDate())
				);
				
				$endDifference = Date::dayDifference(
					$epochStartTruncated, Date::create($end->toDate())
				);
				
				$result = ($endDifference - $startDifference) / $this->days;
				
				
			} elseif ($this->months) {
				
				$startMonthsCount = $start->getYear() * 12 + ($start->getMonth() - 1);
				$endMonthsCount = $end->getYear() * 12 + ($end->getMonth() - 1);
				
				$result = ($endMonthsCount - $startMonthsCount) / $this->months;
			}
			
			Assert::isEqual(
				$result, (int)$result,
				'floating point mistake, arguments: '
				.$this->name.', '
				.$start->toStamp().', '.$end->toStamp().', '
				.'result: '.var_export($result, true)
			);
			
			return (int)$result;
		}
		
		private function __construct($name)
		{
			$units = self::getUnits();
			
			if (!isset($units[$name]))
				throw new WrongArgumentException(
					"know nothing about unit '$name'"
				);
			
			if (!$units[$name])
				throw new UnimplementedFeatureException(
					'need for complex logic, see manual'
				);
			
			$this->name = $name;
			
			$this->months = $units[$name][0];
			$this->days = $units[$name][1];
			$this->seconds = $units[$name][2];
			
			$notNulls = 0;
			
			if ($this->months > 0)
				++$notNulls;
			
			if ($this->days > 0)
				++$notNulls;
			
			if ($this->seconds > 0)
				++$notNulls;
			
			Assert::isEqual($notNulls, 1, "broken unit '$name'");
		}
		
		private static function getUnits()
		{
			static $result = null;
			
			if (!$result)
				$result = array(
					// name			=> array(months,	days,	seconds)
					'microsecond'	=> array(0,			0,		0.000001),
					'millisecond'	=> array(0,			0,		0.001),
					'second'		=> array(0,			0,		1),
					'minute'		=> array(0,			0,		60),
					'hour'			=> array(0,			0,		3600),
					'day'			=> array(0,			1,		0),
					'week'			=> array(0,			7,		0),
					'month'			=> array(1,			0,		0),
					'year'			=> array(12,		0,		0),
					'decade'		=> array(120,		0,		0),
					'century'		=> array(),
					'millennium'	=> array()
				);
			
			return $result;
		}
		
		private static function getInstance($id)
		{
			static $instances = array();
			
			if (!isset($instances[$id]))
				$instances[$id] = new self($id);
			
			return $instances[$id];
		}
	}
?>
