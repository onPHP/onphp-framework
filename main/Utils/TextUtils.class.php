<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
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
/* $Id$ */

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
			if (mb_strlen($string) < $length)
				return $string;
			else {
				if (!$pos = mb_strpos($string, ' ', $length))
					$pos = $length;
				
				return mb_substr($string, 0, $pos).$append;
			}
		}
		
		/**
		 * @todo move logic to GenericUri
		 * @see http://tools.ietf.org/html/rfc3986#page-38
		 * @author thanks to JanRain, Inc. <openid@janrain.com>
		 * @author http://www.openidenabled.com/openid/libraries/php/
		**/
		public static function normalizeUri($uri)
		{
			$uriMatches = array();
			preg_match(
				'&^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?&',
				$uri, 
				$uriMatches
			);
			
			if (!isset($uriMatches[2]) || $uriMatches[2] === '')
				throw new WrongArgumentException('no scheme');
			
			$scheme = strtolower($uriMatches[2]);
			
			if (!isset($uriMatches[4]) || $uriMatches[4] === '')
				throw new WrongArgumentException('not an absolute uri');
			
			$authorityMatches = array();
			if (
				!preg_match(
					'/^([^@]*@)?([^:]*)(:.*)?/',
					$uriMatches[4],
					$authorityMatches
				)
			)
				throw new WrongArgumentException('invalid authority');
			
			if (isset($authorityMatches[1]))
				$userInfo = $authorityMatches[1];
			else
				$userInfo = '';
			
			$host = strtolower(rawurldecode($authorityMatches[2]));
			
			if (isset($authorityMatches[3])) {
				$port = $authorityMatches[3];
		        if (
		        	($port == ':') 
		        	|| ($scheme == 'http' && $port == ':80') 
		        	|| ($scheme == 'https' && $port == ':443')
		        ) {
		            $port = '';
		        }
			} else
				$port = '';
			
		    $authority = $userInfo . $host . $port;
		    
		    if (isset($uriMatches[5])) {
		    	$path = $uriMatches[5];
		    	$path = preg_replace_callback(
		    		'/%([0-9A-Fa-f]{2})/',
		    		create_function(
		    			'$matched',
		    			'return rawurlencode(rawurldecode($matched[0]));'
		    		),
		    		$path
		    	);
		    	
		    	$path = self::removeDotSegments($path);
		    	
		    	if ($path === '')
		    		$path = '/';
		    } else
		    	$path = '/';
		    
		    if (isset($uriMatches[6])) {
		    	$query = $uriMatches[6];
		    } else
		        $query = '';
			
		    if (isset($uriMatches[8])) {
			    $fragment = $uriMatches[8];
		    } else
		        $fragment = '';
		    
		    return $scheme . '://' . $authority . $path . $query . $fragment;
		}
		
		/**
		 * @deprecated by GenericUri
		**/
		private static function removeDotSegments($path)
		{
			$segments = array();
			
			while ($path) {
				if (strpos($path, '../') === 0) {
					$path = substr($path, 3);
				} else if (strpos($path, './') === 0) {
					$path = substr($path, 2);
				} else if (strpos($path, '/./') === 0) {
					$path = substr($path, 2);
				} else if ($path == '/.') {
					$path = '/';
				} else if (strpos($path, '/../') === 0) {
					$path = substr($path, 3);
					if ($segments) {
						array_pop($segments);
					}
				} else if ($path == '/..') {
					$path = '/';
					if ($segments) {
						array_pop($segments);
					}
				} else if (($path == '..') ||
				($path == '.')) {
					$path = '';
				} else {
					$i = 0;
					if ($path[0] == '/') {
						$i = 1;
					}
					$i = strpos($path, '/', $i);
					if ($i === false) {
						$i = strlen($path);
					}
					$segments[] = substr($path, 0, $i);
					$path = substr($path, $i);
				}
			}
			
			return implode('', $segments);
		}
	}
?>