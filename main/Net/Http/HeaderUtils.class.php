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
	 * Collection of static header functions.
	 * 
	 * @ingroup Http
	**/
	final class HeaderUtils extends StaticFactory
	{
		private static $headerSent		= false;
		private static $redirectSent	= false;
		private static $cacheLifeTime   = 3600;
		private static $headers			= array();
		
		public static function redirectRaw($url)
		{
			header("Location: {$url}");

			self::$headerSent = true;
			self::$redirectSent = true;
		}
		
		public static function redirectBack()
		{
			if (isset($_SERVER['HTTP_REFERER'])) {
				header("Location: {$_SERVER['HTTP_REFERER']}");
				self::$headerSent = true;
				self::$redirectSent = true;
				return $_SERVER['HTTP_REFERER'];
			}

			return false;
		}

		public static function getRequestHeaderList()
		{
			if (!empty(self::$headers))
				return self::$headers;

			if (function_exists('apache_request_headers')) {
				self::$headers = apache_request_headers();
			} else {
				foreach($_SERVER as $key => $value) {
					if (substr($key, 0, 5) == "HTTP_") {
						$name = self::extractHeader($key, "_", 5);
						self::$headers[$name] = $value;
					}
				}
			}

			return self::$headers;
		}

		public static function getRequestHeader($name)
		{
			$name = self::extractHeader($name, "-", 0);
			$list = self::getRequestHeaderList();

			if (isset($list[$name]))
				return $list[$name];

			return null;
		}
		
		public static function getParsedURI(/* ... */)
		{
			if ($num = func_num_args()) {
				$out = self::getURI();
				$uri = null;
				$arr = func_get_args();
				
				for ($i = 0; $i < $num; ++$i)
					unset($out[$arr[$i]]);
				
				foreach ($out as $key => $val) {
					if (is_array($val)) {
						foreach ($val as $k => $v)
							$uri .= "&{$key}[{$k}]={$v}";
					} else
						$uri .= "&{$key}={$val}";
				}

				return $uri;
			}

			return null;
		}
		
		public static function sendCachedHeader()
		{
			header('Cache-control: private, max-age=3600');
			
			header(
				'Expires: '
				.date('D, d M Y H:i:s', date('U') + self::$cacheLifeTime)
				.' GMT'
			);
			
			self::$headerSent = true;
		}

		public static function sendNotCachedHeader()
		{
			header('Cache-control: no-cache');
			header(
				'Expires: '
				.date('D, d M Y H:i:s', date('U') - self::$cacheLifeTime)
				.' GMT'
			);
			
			self::$headerSent = true;
		}
		
		public static function sendContentLength($length)
		{
			Assert::isInteger($length);

			header("Content-Length: {$length}");

			self::$headerSent = true;
		}
		
		public static function sendHttpStatus(HttpStatus $status)
		{
			header($status->toString());

			self::$headerSent = true;
		}

		public static function isHeaderSent()
		{
			return self::$headerSent;
		}
		
		public static function forceHeaderSent()
		{
			self::$headerSent = true;
		}
		
		public static function isRedirectSent()
		{
			return self::$redirectSent;
		}
		
		public static function setCacheLifeTime($cacheLifeTime)
		{
			self::$cacheLifeTime = $cacheLifeTime;
		}
		
		public static function getCacheLifeTime()
		{
			return self::$cacheLifeTime;
		}

		private static function getURI()
		{
			$out = null;
			
			parse_str($_SERVER['QUERY_STRING'], $out);
			
			return $out;
		}

		private static function extractHeader($name, $delimiter, $length)
		{
			return
				str_replace(
					" ",
					"-",
					ucwords(
						strtolower(
							str_replace(
								$delimiter,
								" ",
								$length
									? substr($name, $length)
									: $name
							)
						)
					)
				);
		}
	}
?>