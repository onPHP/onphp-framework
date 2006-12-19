<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *    UrlSaveBase64* functions borrowed from comments on                   *
 *    http://www.php.net/manual/en/function.base64-encode.php              *
 *    by massimo dot scamarcia at gmail dot com                            *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class TextUtils extends StaticFactory
	{
		public static function friendlyFileSize($size, $order = 0)
		{
			static $units = array('', 'k' , 'm', 't', 'p');
			
			if ($size >= 1024 && $order < 4)
				return self::friendlyFileSize($size / 1024, $order + 1);
			elseif (isset($units[$order]))
				return round($size, 2).$units[$order];
				
			return $size;
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
	}
?>