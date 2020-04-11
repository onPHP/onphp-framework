<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 *    UrlSaveBase64* functions borrowed from comments on                   *
 *    http://www.php.net/manual/en/function.base64-encode.php              *
 *    by massimo dot scamarcia at gmail dot com                            *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class TextUtils extends StaticFactory
	{
		public static function friendlyFileSize(
			$size, $precision = 2,
			$units = array(null, 'k' , 'M', 'G', 'T', 'P'),
			$spacePunctuation = false
		)
		{
			if ($size > 0) {
				$index = min((int) log($size, 1024), count($units) - 1);
				
				return
					round($size / pow(1024, $index), $precision)
					.($spacePunctuation ? ' ' : null)
					.$units[$index];
			}
			
			return 0;
		}
		
		public static function getRootFromUrl($url)
		{
			if (
				strpos($url, '//') !== false
				&& (strpos($url, '//') + 2) < strlen($url)
			)
				$offset = strpos($url, '//') + 2;
			else
				$offset = 0;
			
			return substr($url, 0, strpos($url, '/', $offset) + 1);
		}
		
		public static function getPathFromUrl($url)
		{
			$parsed = parse_url($url);
			
			if ($parsed === false or !isset($parsed['path']))
				return '/';
			else
				return $parsed['path'];
		}
		
		public static function urlSafeBase64Encode($string)
		{
			return
				str_replace(
					array('+', '/' , '='),
					array('-', '_', ''),
					base64_encode($string)
				);
		}
		
		public static function urlSafeBase64Decode($string)
		{
			$data = str_replace(
				array('-', '_'),
				array('+', '/'),
				$string
			);
			
			$mod4 = strlen($data) % 4;
			
			if ($mod4) {
				$data .= substr('====', $mod4);
			}
			
			return base64_decode($data);
		}
		
		public static function upFirst($string)
		{
			$firstOne = mb_strtoupper(mb_substr($string, 0, 1));
			
			return $firstOne.mb_substr($string, 1);
		}
		
		public static function downFirst($string)
		{
			$firstOne = mb_strtolower(mb_substr($string, 0, 1));
			
			return $firstOne.mb_substr($string, 1);
		}
		
		public static function cutOnSpace($string, $length, $append = null)
		{
			$stringLength = mb_strlen($string);
			
			if ($stringLength < $length)
				return $string;
			else {
				if (!$pos = mb_strpos($string, ' ', $length))
					$pos = $stringLength;
				
				return mb_substr($string, 0, $pos).$append;
			}
		}
		
		/**
		 * @deprecated by GenericUri::normalize
		**/
		public static function normalizeUri($uri)
		{
			return GenericUri::create()->
				parse($uri, true)->
				normalize()->
				toString();
		}
		
		public static function hex2Binary($hex)
		{
			$length = strlen($hex);
			
			Assert::isEqual($length % 2, 0);
			
			$out = null;
			for ($i = 0; $i < $length; $i += 2) {
				$out .= pack('C', hexdec(substr($hex, $i, 2)));
			}
			
			return $out;
		}
		
		/**
		 * @see http://www.w3.org/TR/REC-html40/charset.html#entities
		 * @see http://www.w3.org/TR/REC-html40/sgml/entities.html
		 * @see http://php.net/htmlentities
		 * 
		 * htmlentities() do not support hexadecimal numeric character
		 * references yet.
		**/
		public static function safeAmp($text)
		{
			$result = preg_replace(
				'/&(?!(#(([0-9]+)|(x[0-9A-F]+))'
				.'|([a-z][a-z0-9]*));)/i',
				'&amp;',
				$text
			);
			
			return $result;
		}
		
		public static function friendlyNumber($number, $delimiter = ' ')
		{
			$localeInfo = localeconv();
			
			$decimalPoint = $localeInfo['decimal_point'];
			
			$number = (string) $number;
			
			$parts = explode($decimalPoint, $number);
			
			$integer = abs(array_shift($parts));
			
			$minus = $number < 0 ? '-' : '';
			
			$floatDiff = array_shift($parts);
			
			if ($integer > 9999) {
				$orders = array();
				
				while ($integer > 0) {
					$order = $integer % 1000;
					$integer = (int) ($integer / 1000);
					
					if ($integer > 0)
						$orders[] = sprintf('%03d', $order);
					else
						$orders[] = (string) $order;
				}
				
				$result = implode($delimiter, array_reverse($orders));
				
			} else
				$result = (string) $integer;
			
			if ($floatDiff)
				$result = $result.$decimalPoint.$floatDiff;
			
			return $minus.$result;
		}
	}
?>