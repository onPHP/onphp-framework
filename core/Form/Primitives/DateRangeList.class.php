<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov, Igor V. Gulyaev    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class DateRangeList extends BasePrimitive implements Stringable
	{
		public function import($scope)
		{
			if (
				!isset($scope[$this->name])
				|| !is_array($scope[$this->name])
				|| empty($scope[$this->name])
				|| (
					count($scope[$this->name]) == 1
					&& !current($scope[$this->name])
				)
			)
				return null;
				
			$array = $scope[$this->name];
			$list = array();

			foreach ($array as $string)
				if (array() !== self::stringToDateRangeList($string))
					foreach (self::stringToDateRangeList($string) as $range)
						$list[] = $range;
			
			$this->value = $list;

			return ($this->value !== array());
		}
		
		public function toString()
		{
			if ($this->value) {
				$out = array();
				
				foreach ($this->value as $range)
					$out[] = $range->toDateString();
					
				return implode(', ', $out);
			}
			
			return null;
		}
		
		public static function stringToDateRangeList($string)
		{
			$list = array();
			
			if ($string) {
				if (strpos($string, ',') !== false)
					$dates = explode(',', $string);
				else
					$dates = array($string);
				
				foreach ($dates as $date) {
					try {
						$list[] = self::makeRange($date);
					} catch (WrongArgumentException $e) {
						// ignore?
					}
				}
			}
			
			return $list;
		}
		
		public static function printRange(DateRange $range, $delimiter = '-')
		{
			$rangeString = null;
			
			if ($range->getStart())
				$rangeString .= $range->getStart()->toDate($delimiter);
				
			$rangeString .= ' - ';
			
			if ($range->getEnd())
				$rangeString .= $range->getEnd()->toDate($delimiter);
				
			return $rangeString;
		}
		
		public static function makeRange($string)
		{
			if (
				(substr_count($string, ' - ') === 1)
				|| (substr_count($string, '-') === 1)
			) {
				$delimiter = ' - ';
				
				if (substr_count($string, '-') === 1)
					$delimiter = '-';
				
				list($start, $finish) = explode($delimiter, $string, 2);
				
				$start = self::toDate(trim($start));
				$finish = self::toDate(trim($finish));
				
				if ($start || $finish) {
					
					$range = new DateRange();
					
					$range =
						DateRange::create()->
						lazySet($start, $finish);
					
					return $range;
					
				} elseif ($string == ' - ')
					return DateRange::create();
				
			} elseif ($single = self::toDate(trim($string)))
				return
					DateRange::create()->
					setStart($single)->
					setEnd($single);
			
			throw new WrongArgumentException(
				"unknown string format '{$string}'"
			);
		}
		
		private static function toDate($date)
		{
			if (strpos($date, '.') !== false) {
				
				$dots = substr_count($date, '.');
				
				$year = null;
				
				if ($dots == 2) {
					list($day, $month, $year) = explode('.', $date, ($dots + 1));
					
					if (strlen($day) > 2) {
						$tmp = $year;
						$year = $day;
						$day = $tmp;
					}
				} else
					list($day, $month) = explode('.', $date, ($dots + 1));
				
				if (strlen($day) == 1)
					$day = "0{$day}";
				
				if ($month === null)
					$month = date('m');
				elseif (strlen($month) == 1)
					$month = "0{$month}";
				
				if ($year === null)
					$year = date('Y');
				// we're all dead in 2100+ anyway
				elseif (strlen($year) === 2)
					$year = "20{$year}";
				
				$date = $year.$month.$day;
			}
			
			$lenght = strlen($date);
			
			try {
				if ($lenght > 4)
					return new Timestamp(strtotime($date));
				elseif ($lenght === 4) {
					$date = substr($date, 2).'-'.substr($date, 0, 2);
					
					return new Timestamp(strtotime(date('Y-').$date));
				} elseif (($lenght == 2) || ($lenght == 1)) {
					return new Timestamp(strtotime(date('Y-m-').$date));
				}
			} catch (WrongArgumentException $e) {
				// seems to be unparseable
			}
			
			return null;
		}
	}
?>