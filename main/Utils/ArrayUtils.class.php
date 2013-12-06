<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	namespace Onphp;

	final class ArrayUtils extends StaticFactory
	{
		/// orders $objects list by $ids order
		public static function regularizeList($ids, $objects)
		{
			if (!$objects)
				return array();
			
			$result = array();
			
			$objects = self::convertObjectList($objects);
			
			foreach ($ids as $id)
				if (isset($objects[$id]))
					$result[] = $objects[$id];
			
			return $result;
		}
		
		public static function convertObjectList($list = null, $getter = 'getId')
		{
			$out = array();
			
			if (!$list)
				return $out;
			
			foreach ($list as $obj)
				$out[$obj->{$getter}()] = $obj;
			
			return $out;
		}
		
		public static function getIdsArray($objectsList)
		{
			$out = array();
			
			if (!$objectsList)
				return $out;
			
			Assert::isInstance(
				current($objectsList), '\Onphp\Identifiable',
				'only identifiable lists accepted'
			);
			
			foreach ($objectsList as $object)
				$out[] = $object->getId();
			
			return $out;
		}
		
		public static function &convertToPlainList($list, $key)
		{
			$out = array();
			
			foreach ($list as $obj)
				$out[] = $obj[$key];
			
			return $out;
		}
		
		public static function getArrayVar(&$array, $var)
		{
			if (isset($array[$var]) && !empty($array[$var])) {
				$out = &$array[$var];
				return $out;
			}
			
			return null;
		}
		
		public static function columnFromSet($column, $array)
		{
			Assert::isArray($array);
			$result = array();
			
			foreach ($array as $row)
				if (isset($row[$column]))
					$result[] = $row[$column];
			
			return $result;
		}
		
		public static function mergeUnique(/* ... */)
		{
			$arguments = func_get_args();
			
			Assert::isArray(reset($arguments));
			
			return array_unique(
				call_user_func_array(
					'array_merge',
					$arguments
				)
			);
		}
		
		public static function countNonemptyValues($array)
		{
			Assert::isArray($array);
			$result = 0;
			
			foreach ($array as $value)
				if (!empty($value))
					++$result;
			
			return $result;
		}
		
		public static function isEmpty(array $array)
		{
			foreach ($array as $key => $value)
				if ($value !== null)
					return false;
			
			return true;
		}
		
		/**
		 * in: array(1, 2, 3, 4)
		 * out: array(1 => array(2 => array(3 => 4)))
		**/
		public static function flatToDimensional($array)
		{
			if (!$array)
				return null;
			
			Assert::isArray($array);
			
			$first = array_shift($array);
			
			if (!$array)
				return $first;
			
			return array($first => self::flatToDimensional($array));
		}
		
		public static function mergeRecursiveUnique($one, $two)
		{
			if (!$one)
				return $two;
			
			Assert::isArray($one);
			Assert::isArray($two);
			
			$result = $one;
			
			foreach ($two as $key => $value) {
				
				if (is_integer($key)) {
					$result[] = $value;
				} elseif (
					isset($one[$key])
					&& is_array($one[$key])
					&& is_array($value)
				) {
					$result[$key] = self::mergeRecursiveUnique($one[$key], $value);
				} else {
					$result[$key] = $value;
				}
			}
			
			return $result;
		}
		
		/**
		 * @deprecated by array_combine($array, $array)
		**/
		public static function getMirrorValues($array)
		{
			Assert::isArray($array);
			
			$result = array();
			
			foreach ($array as $value) {
				Assert::isTrue(
					is_integer($value) || is_string($value),
					'only integer or string values accepted'
				);
				
				$result[$value] = $value;
			}
			
			return $result;
		}

		// TODO: drop Reflection
		public static function mergeSortedLists(
			$list1,
			$list2,
			Comparator $comparator,
			$compareValueGetter = null,
			$limit = null
		)
		{
			$list1Size = count($list1);
			$list2Size = count($list2);

			$i = $j = $k = 0;

			$newList = array();

			while ($i < $list1Size && $j < $list2Size) {
				if (
					$limit
					&& $k == $limit
				)
					return $newList;

				if (!$compareValueGetter)
					$compareResult = $comparator->compare(
						$list1[$i], $list2[$j]
					);
				else
					$compareResult = $comparator->compare(
						$list1[$i]->{$compareValueGetter}(),
						$list2[$j]->{$compareValueGetter}()
					);
				
				// list1 elt < list2 elt
				if ($compareResult < 0)
					$newList[$k++] = $list2[$j++];
				else
					$newList[$k++] = $list1[$i++];
			}

			while ($i < $list1Size) {
				if (
					$limit
					&& $k == $limit
				)
					return $newList;
				
				$newList[$k++] = $list1[$i++];
			}

			while ($j < $list2Size) {
				if (
					$limit
					&& $k == $limit
				)
					return $newList;
				
				$newList[$k++] = $list2[$j++];
			}

			return $newList;
		}
	}
?>