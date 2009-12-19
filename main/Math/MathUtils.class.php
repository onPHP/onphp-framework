<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Math
	**/
	final class MathUtils extends StaticFactory
	{
		/**
		 * Fisher Yates shuffle algorithm implementation
		**/
		public static function fisherYatesShuffle(&$elts)
		{
			$num = count($elts);
			
			for ($i = $num - 1; $i > 0; --$i) {
				$j = mt_rand(0, $i);
				
				$tmp		= $elts[$i];
				$elts[$i]	= $elts[$j];
				$elts[$j]	= $tmp;
			}
		}
		
		public static function makeCartesianProduct(
			$arrays, $generateHash = false
		)
		{
			$result = array();
			
			$size = (sizeof($arrays) > 0) ? 1 : 0;
			
			foreach ($arrays as $array)
				$size *= sizeof($array);
			
			$keys = array_keys($arrays);
			
			foreach ($keys as $key)
				$tmpArrays[] = $arrays[$key];
			
			for ($i = 0; $i < $size; $i++) {
				$result[$i] = array();
				
				for ($j = 0; $j < sizeof($tmpArrays); $j++) {
           			$result[$i][$keys[$j]] = current($tmpArrays[$j]);
				}
				
				if ($generateHash)
					$result[$i]['hash'] = md5(implode('_', $result[$i]));
				
				for ($j = (sizeof($tmpArrays) - 1); $j >= 0; $j--) {
					if (next($tmpArrays[$j])) {
               			break;
           			} else
               			reset($tmpArrays[$j]);
				}
			}
			
			return $result;
		}
		
		public static function randFloat($min, $max)
		{
			return ($min + lcg_value() * (abs($max - $min)));
		}
		
		public static function alignByBase($value, $base, $ceil = false)
		{
			$function = $ceil ? 'ceil' : 'floor';
			
			return $function($value / $base) * $base;
		}

		/**
		 * G. E. P. Box and Mervin E. Muller, A Note on the Generation of
		 * Random Normal Deviates, The Annals of Mathematical Statistics
		 * (1958), Vol. 29, No. 2 pp. 610-611
		**/
		public static function randomNormalStd()
		{
			do {
				$x = rand() / getrandmax() * 2 - 1;
				$y = rand() / getrandmax() * 2 - 1;

				$r = ($x * $x) + ($y * $y);

			} while (($r > 1) || ($x + $y == 0));

			$z = $x * sqrt(-2 * log($r) / $r);

			return $z;
		}
		
		public static function getStandardDeviation(array $list)
		{
			return self::getStandardDeviationP($list, count($list) - 1);
		}
		
		public static function getStandardDeviationP(array $list, $size = null)
		{
			$tempSum = 0;
			
			if (!$size)
				$size = count($list);
			
			Assert::isPositiveInteger($size);
			
			$averageValue = self::getAverage($list, $size);
			
			for ($i = 0; $i < $size; $i++)
				$tempSum += pow($list[$i] - $averageValue, 2);
		
			return sqrt($tempSum / $size);
		}
		
		public static function getAbsoluteDeviation(array $list)
		{
			$value = 0;
			
			$averageValue = self::getAverage($list);
			
			foreach ($list as $elt)
				$value += abs($elt - $averageValue);
			
			return $value / count($list);
		}
		
		public static function getMeanDeviation($elt, $averageValue)
		{
			return $elt - $averageValue;
		}
		
		public static function getAverage(array $list, $size = null)
		{
			if (!$size)
				$size = count($list);
			
			Assert::isPositiveInteger($size);
			
			$tempSum = 0;
			
			for ($i = 0; $i < $size; $i++)
				$tempSum += $list[$i];
			
			return $tempSum / $size;
		}
		
		public static function getCovariance(array $list1, array $list2)
		{
			$list1Size = count($list1);
			$list2Size = count($list2);
			
			Assert::isEqual($list1Size, $list2Size, 'Array sizes should be equals!');
			
			$list1AverageValue = self::getAverage($list1);
			$list2AverageValue = self::getAverage($list2);
			
			$tempSum = 0;
			
			for ($i = 0; $i < $list1Size; $i++) {
				$elt1Dev = self::getMeanDeviation($list1[$i], $list1AverageValue);
				$elt2Dev = self::getMeanDeviation($list2[$i], $list2AverageValue);
				
				$tempSum += $elt1Dev * $elt2Dev;
			}
			
			return $tempSum / ($list1Size - 1);
		}
		
		public static function getPearsonProductMomentCorrelation(array $list1, array $list2)
		{
			return
				self::getCovariance($list1, $list2)
				/ (
					self::getStandardDeviation($list1)
					* self::getStandardDeviation($list2)
				);
		}
	}
?>