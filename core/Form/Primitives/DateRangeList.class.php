<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Igor V. Gulyaev    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class DateRangeList extends BasePrimitive implements Stringable
	{
		protected $value = array();
		
		/**
		 * @return DateRangeList
		**/
		public function clean()
		{
			parent::clean();
			
			$this->value = array();
			
			return $this;
		}
		
		public function import($scope)
		{
			if (
				empty($scope[$this->name])
				|| !is_array($scope[$this->name])
				|| (
					count($scope[$this->name]) == 1
					&& !current($scope[$this->name])
				)
			)
				return null;
			
			$this->raw = $scope[$this->name];
			$this->imported = true;
			$list = array();
			
			foreach ($this->raw as $string) {
				$rangeList = self::stringToDateRangeList($string);
				
				if ($rangeList)
					foreach ($rangeList as $range)
						$list[] = $range;
			}
			
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
		
		/**
		 * @throws WrongArgumentException
		 * @return DateRange
		**/
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
					
				} elseif (trim($string) == '-')
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
		
		/**
		 * @throws WrongArgumentException
		 * @return Date
		**/
		private static function toDate($date)
		{
			if (strpos($date, '.') !== false) {
				
				$fieldCount = substr_count($date, '.') + 1;
				
				$year = null;
				
				if ($fieldCount == 3) {
					list($day, $month, $year) = explode('.', $date, $fieldCount);
					
					if (strlen($day) > 2) {
						$tmp = $year;
						$year = $day;
						$day = $tmp;
					}
				} else
					list($day, $month) = explode('.', $date, $fieldCount);
				
				if (strlen($day) == 1)
					$day = "0{$day}";
				
				if ($month === null)
					$month = date('m');
				elseif (strlen($month) == 1)
					$month = "0{$month}";
				
				$currentYear = date('Y');
				if ($year === null)
					$year = $currentYear;
				elseif (strlen($year) === 2)
					$year = substr_replace($currentYear, $year, -2, 2);
				
				$date = $year.$month.$day;
			}
			
			$lenght = strlen($date);
			
			if ($lenght > 4) {
				return new Date(strtotime($date));
			} elseif ($lenght === 4) {
				return new Date(
					strtotime(
						date('Y-').substr($date, 2).'-'.substr($date, 0, 2)
					)
				);
			} elseif (($lenght == 2) || ($lenght == 1)) {
				return new Date(strtotime(date('Y-m-').$date));
			}
			
			return null;
		}
		
		public function exportValue()
		{
			// cannot use toString() because of different delimiters
			throw new UnimplementedFeatureException();
		}
	}
