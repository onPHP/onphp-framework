<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	define('ONPHP_CLASS_CACHE_CHECKSUM', '__occc');
	
	/* void */ function __autoload($classname)
	{
		// numeric indexes for directories, literal indexes for classes
		static $cache 		= null;
		static $path 		= null;
		static $checksum 	= null;
		
		if (strpos($classname, "\0") !== false) {
			// we can not avoid fatal error in this case
			return /* void */;
		}
		
		$currentPath = get_include_path();
		
		if ($currentPath != $path) {
			$checksum = crc32($currentPath);
			$path = $currentPath;
		}
		
		$cacheFile = ONPHP_CLASS_CACHE.$checksum.'.occ';
		
		if ($cache && ($cache[ONPHP_CLASS_CACHE_CHECKSUM] <> $checksum))
			$cache = null;
		
		if (!$cache) {
			try {
				$cache = unserialize(@file_get_contents($cacheFile, false));
			} catch (BaseException $e) {
				/* ignore */
			}
			
			if (isset($cache[$classname])) {
				try {
					include $cache[$cache[$classname]].$classname.EXT_CLASS;
					return /* void */;
				} catch (ClassNotFoundException $e) {
					throw $e;
				} catch (BaseException $e) {
					$cache = null;
				}
			}
		}
		
		if (!$cache) {
			$cache = array();
			$dirCount = 0;
			
			foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
				$cache[$dirCount] = realpath($directory).DIRECTORY_SEPARATOR;
				
				if ($paths = glob($cache[$dirCount].'*'.EXT_CLASS, GLOB_NOSORT)) {
					foreach ($paths as $class) {
						$class = basename($class, EXT_CLASS);
						
						// emulating include_path searching behaviour
						if (!isset($cache[$class]))
							$cache[$class] = $dirCount;
					}
				}
				
				++$dirCount;
			}
			
			$cache[ONPHP_CLASS_CACHE_CHECKSUM] = $checksum;
			
			if (
				is_writable(dirname($cacheFile))
				&& (
					!file_exists($cacheFile)
					|| is_writable($cacheFile)
				)
			)
				file_put_contents($cacheFile, serialize($cache));
		}
		
		if (isset($cache[$classname])) {
			$fileName = $cache[$cache[$classname]].$classname.EXT_CLASS;
			
			try {
				include $fileName;
			} catch (BaseException $e) {
				if (is_readable($fileName))
					// class compiling failed
					throw $e;
				else {
					// cache is not actual
					$cache[ONPHP_CLASS_CACHE_CHECKSUM] = null;
					__autoload($classname);
				}
			}
		} else {
			// ok, last chance to find class in non-cached include_path
			try {
				include $classname.EXT_CLASS;
				$cache[ONPHP_CLASS_CACHE_CHECKSUM] = null;
				return /* void */;
			} catch (BaseException $e) {
				__autoload_failed($classname, $e->getMessage());
			}
		}
	}
?>