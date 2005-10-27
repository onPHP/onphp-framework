<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Collection of static header functions
	 * 
	 * @package		Utils
	 * @version		1.0
	 * @copyright	2005
	**/
	class HeaderUtils
	{
		private static $headerSent		= false;
		private static $redirectSent	= false;
		private static $cacheLifeTime   = 3600;
		
		public static function redirectRaw($url)
		{
			header("Location: {$url}");

			self::$headerSent = true;
			self::$redirectSent = true;
		}

		public static function redirect(BaseModule $mod)
		{
			$qs = '';
			
			if ($mod->getParameters())
				foreach ($mod->getParameters() as $key => $val)
					$qs .= "&{$key}={$val}";
			
			$url = (defined('ADMIN_AREA') ? PATH_WEB_ADMIN : PATH_WEB).'?area='.$mod->getName().$qs;
			
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
				$uri = '';
				$arr = func_get_args();
				
				for ($i = 0; $i < $num; $i++)
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
			Header("Cache-control: private, max-age=3600");
			Header("Expires: " . gmdate("D, d M Y H:i:s", date("U") + self::$cacheLifeTime) . " GMT");
			self::$headerSent = true;
		}

		public static function sendNotCachedHeader()
		{
			Header("Expires: " . gmdate("D, d M Y H:i:s", date("U") - 3600) . " GMT");
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