<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Collection of static header functions.
	 * 
	 * @ingroup Utils
	**/
	final class HeaderUtils extends StaticFactory
	{
		private static $headerSent		= false;
		private static $redirectSent	= false;
		private static $cacheLifeTime   = 3600;
		
		private static $httpStatus 		= array (
											100 => "HTTP/1.1 100 Continue",
											101 => "HTTP/1.1 101 Switching Protocols",
											200 => "HTTP/1.1 200 OK",
											201 => "HTTP/1.1 201 Created",
											202 => "HTTP/1.1 202 Accepted",
											203 => "HTTP/1.1 203 Non-Authoritative Information",
											204 => "HTTP/1.1 204 No Content",
											205 => "HTTP/1.1 205 Reset Content",
											206 => "HTTP/1.1 206 Partial Content",
											300 => "HTTP/1.1 300 Multiple Choices",
											301 => "HTTP/1.1 301 Moved Permanently",
											302 => "HTTP/1.1 302 Found",
											303 => "HTTP/1.1 303 See Other",
											304 => "HTTP/1.1 304 Not Modified",
											305 => "HTTP/1.1 305 Use Proxy",
											307 => "HTTP/1.1 307 Temporary Redirect",
											400 => "HTTP/1.1 400 Bad Request",
											401 => "HTTP/1.1 401 Unauthorized",
											402 => "HTTP/1.1 402 Payment Required",
											403 => "HTTP/1.1 403 Forbidden",
											404 => "HTTP/1.1 404 Not Found",
											405 => "HTTP/1.1 405 Method Not Allowed",
											406 => "HTTP/1.1 406 Not Acceptable",
											407 => "HTTP/1.1 407 Proxy Authentication Required",
											408 => "HTTP/1.1 408 Request Time-out",
											409 => "HTTP/1.1 409 Conflict",
											410 => "HTTP/1.1 410 Gone",
											411 => "HTTP/1.1 411 Length Required",
											412 => "HTTP/1.1 412 Precondition Failed",
											413 => "HTTP/1.1 413 Request Entity Too Large",
											414 => "HTTP/1.1 414 Request-URI Too Large",
											415 => "HTTP/1.1 415 Unsupported Media Type",
											416 => "HTTP/1.1 416 Requested range not satisfiable",
											417 => "HTTP/1.1 417 Expectation Failed",
											500 => "HTTP/1.1 500 Internal Server Error",
											501 => "HTTP/1.1 501 Not Implemented",
											502 => "HTTP/1.1 502 Bad Gateway",
											503 => "HTTP/1.1 503 Service Unavailable",
											504 => "HTTP/1.1 504 Gateway Time-out"       
										);
		
		public static function redirectRaw($url)
		{
			header("Location: {$url}");

			self::$headerSent = true;
			self::$redirectSent = true;
		}

		public static function redirect(BaseModule $mod)
		{
			$qs = null;
			
			if ($mod->getParameters())
				foreach ($mod->getParameters() as $key => $val)
					$qs .= "&{$key}={$val}";
			
			$url =
				(defined('ADMIN_AREA')
					? PATH_WEB_ADMIN
					: PATH_WEB)
				.'?area='
				.$mod->getName()
				.$qs;
			
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
				return true;
			} else
				return false;
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
			
			header(
				"Content-Length: {$length}"
			);
			
			self::$headerSent = true;
		}
		
		public static function sendHttpStatus($statusCode)
		{
			if (!isset(self::$httpStatus[$statusCode]))
				throw new WrongArgumentException($statusCode.' wrong status code');
			
			header(self::$httpStatus[$statusCode]);
			
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
	}
?>