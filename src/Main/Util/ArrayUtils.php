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

namespace OnPHP\Main\Util;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Base\StaticFactory;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\Comparator;

/**
 * @ingroup Util
**/
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

	/**
	 * @todo - добавить третий аргумент для аналогии с array_column
	 */
	public static function convertObjectList($list = null, $getter = 'getId')
	{
		$out = array();

		if (!$list)
			return $out;

		foreach ($list as $obj)
			$out[$obj->{$getter}()] = $obj;

		return $out;
	}

	/**
	 * @param array $objectsList
	 * @return Identifiable[]
	 * @throws WrongArgumentException
	 */
	public static function getIdsArray(array $objectsList): array
	{
		if (empty($objectsList)) {
			return [];
		}

		return array_map(function ($objectItem) {
			Assert::isInstance($objectItem, Identifiable::class,'only identifiable lists accepted');
			return $objectItem->getId();
		}, $objectsList);
	}

	/**
	 * @param array $list
	 * @param mixed $key
	 * @return array
	 */
	public static function convertToPlainList(array $list, $key): array
	{
		return array_filter(
			array_column($list, $key)
		);
	}

	/**
	 * @param $array
	 * @param $var
	 * @return mixed|null
	 */
	public static function getArrayVar(&$array, $var)
	{
		if (isset($array[$var]) && !empty($array[$var])) {
			$out = &$array[$var];
			return $out;
		}

		return null;
	}

	/**
	 * @param $column
	 * @param array $array
	 * @return array
	 * @deprecated by [[self::convertToPlainList]]
	 */
	public static function columnFromSet($column, array $array): array
	{
		return array_filter(
			array_column($array, $column)
		);
	}

	/**
	 * @param array ...$arguments
	 * @return array
	 * @throws WrongArgumentException
	 */
	public static function mergeUnique(...$arguments): array
	{
		array_map(function ($array) {
			Assert::isArray($array);
		}, $arguments);

		return array_unique(
			array_merge(...$arguments)
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
