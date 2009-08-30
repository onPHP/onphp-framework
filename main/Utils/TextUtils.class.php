<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
				
			return substr(
				$url,
				0,
				strpos(
					$url,
					'/',
					$offset
				) + 1
			);
		}
		
		public static function getPathFromUrl($url)
		{
			$parsed = parse_url($url);
			if ($parsed === false or !isset($parsed['path']))
				return '/';
			else
				return $parsed['path'];
		}
		
		// FIXME: csc broken
		public static function UrlSafeBase64Encode($string)
		{
			return
				str_replace(
					array('+', '/' , '='),
					array('-', '_', ''),
					base64_encode($string)
				);
		}
		
		// FIXME: csc broken
		public static function UrlSafeBase64Decode($string)
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
	}
?>