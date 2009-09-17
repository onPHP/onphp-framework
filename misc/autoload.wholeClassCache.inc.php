<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	// we must duplicate this one here, otherwise any fail will be silent
	class BaseException extends Exception
	{
		public function __toString()
		{
			return
				"[$this->message] in: \n".
				$this->getTraceAsString();
		}
	}
	
	/**
	 * this class cache type useful only if you can't turn off
	 * stat syscalls in your opcode cacher,
	 * otherwise use classPathCache
	 * 
	 * cache directory *must* be created before using this cacher,
	 * or it will fail with fatal error
	**/
	/* void */ function __autoload($classname)
	{
		static $path = null;
		static $checksum = null;
		static $included = array();
		
		if (strpos($classname, "\0") !== false) {
			/* are you sane? */
			return;
		}
		
		$currentPath = get_include_path();
		
		if ($currentPath != $path) {
			$checksum = crc32($currentPath);
			$path = $currentPath;
		}
		
		$cacheFile = ONPHP_CLASS_CACHE.$checksum.'.occ';
		
		if (!isset($included[$cacheFile])) {
			try {
				include $cacheFile;
				$included[$cacheFile] = true;
			} catch (BaseException $e) {
				/* ignore */
			}
		}
		
		if (!class_exists($classname)) {
			static $pid = null;
			
			if (!$pid) {
				$pid = getmypid();
				
				register_shutdown_function('__autoload_cleanup');
			}
			
			try {
				$classPath = null;
				
				foreach (
					explode(PATH_SEPARATOR, get_include_path())
					as $directory
				) {
					$location = $directory.'/'.$classname.EXT_CLASS;
					
					if (is_readable($location)) {
						$classPath = $location;
						break;
					}
				}
				
				if (!$classPath)
					throw new BaseException('failed to find requested class');
				
				$class = file_get_contents($classPath);
				
				eval('?>'.$class);
			} catch (BaseException $e) {
				return __autoload_failed($classname, $e->getMessage());
			}
			
			file_put_contents($cacheFile.'-'.$pid, $class, FILE_APPEND);
			
			$included[$cacheFile] = true;
		}
	}
	
	/* void */ function __autoload_cleanup()
	{
		$pid = getmypid();
		
		if ($paths = glob(ONPHP_CLASS_CACHE.'*-'.$pid, GLOB_NOSORT)) {
			foreach ($paths as $file) {
				rename($file, ONPHP_CLASS_CACHE.basename($file, '-'.$pid));
			}
		}
	}
?>