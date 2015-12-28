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

/**
 * @ingroup Utils
 **/
final class UrlParamsUtils extends StaticFactory
{
    /**
     * @deprecated to support old convert method in CurlHttpClient
     * @param array $array
     * @return string
     */
    public static function toStringOneDeepLvl($array)
    {
        Assert::isArray($array);
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $valueKey => $simpleValue) {
                    $result[] =
                        $key . '[' . $valueKey . ']=' . urlencode($simpleValue);
                }
            } else {
                $result[] = $key . '=' . urlencode($value);
            }
        }

        return implode('&', $result);
    }

    public static function toString($array)
    {
        $sum = function ($left, $right) { return $left . '=' . urlencode($right); };
        $params = self::toParamsList($array, true);
        return implode('&',
            array_map($sum, array_keys($params), $params)
        );
    }

    public static function toParamsList($array, $encodeKey = false)
    {
        $result = [];

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
                ? ($keyPrefix . '[' . $filteredKey . ']')
                : $filteredKey;

            if (is_array($value)) {
                self::argumentsToParams($value, $result, $fullKey, $encodeKey);
            } else {
                $result[$fullKey] = $value;
            }
        }
    }
}

