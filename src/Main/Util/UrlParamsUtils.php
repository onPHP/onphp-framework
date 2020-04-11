<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util;

use OnPHP\Core\Base\StaticFactory;
use OnPHP\Core\Base\Assert;

/**
 * @ingroup Utils
**/
final class UrlParamsUtils extends StaticFactory
{
	public static function toString($array)
	{
		$sum = function ($left, $right) {return $left.'='.urlencode($right);};
		$params = self::toParamsList($array, true);
		return implode('&',
			array_map($sum, array_keys($params), $params)
		);
	}

	public static function toParamsList($array, $encodeKey = false)
	{
		$result = array();

		self::argumentsToParams($array, $result, '', $encodeKey);

		return $result;
	}

	private static function argumentsToParams(
		$array,
		&$result,
		$keyPrefix,
		$encodeKey = false
	) {
		foreach ($array as $key => $value) {
			$filteredKey = $encodeKey ? urlencode($key) : $key;
			$fullKey = $keyPrefix
				? ($keyPrefix.'['.$filteredKey.']')
				: $filteredKey;

			if (is_array($value)) {
				self::argumentsToParams($value, $result, $fullKey, $encodeKey);
			} else {
				$result[$fullKey] = $value;
			}
		}
	}
}
?>