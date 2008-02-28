<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class ArrayUtils extends StaticFactory
	{
		public static function convertObjectList($list = null)
		{
			$out = array();

			if (!$list)
				return $out;
				
			foreach ($list as $obj)
				$out[$obj->getId()] = $obj;

			return $out;
		}
		
		public static function getIdsArray($objectsList)
		{
			$out = array();
			
			if (!$objectsList)
				return $out;
			
			Assert::isTrue(
				current($objectsList) instanceof Identifiable,
				'only identifiable lists accepted'
			);
			
			foreach ($objectsList as $object)
				$out[] = $object->getId();

			return $out;
		}
		
		public static function &convertToPlainList(&$list, $key)
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
		
		public static function columnFromSet($column, &$array)
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
	}
?>