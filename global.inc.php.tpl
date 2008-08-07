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
/* $Id$ */

	// sample system-wide configuration file
	
	function error2Exception($code, $string, $file, $line, $context)
	{
		throw new BaseException($string, $code);
	}
	
	/* void */ function __autoload_failed($classname, $message)
	{
		eval(
			'class '.$classname.'{/*_*/}'
			.'if (!class_exists("ClassNotFoundException", false)) { '
			.'class ClassNotFoundException extends BaseException {/*_*/} }'
			.'throw new ClassNotFoundException("'.$classname.': '.$message.'");'
		);
	}
	
	// overridable constant, don't forget for trailing slash
	// also you may consider using /dev/shm/ for cache purposes
	if (!defined('ONPHP_TEMP_PATH'))
		define(
			'ONPHP_TEMP_PATH',
			sys_get_temp_dir().DIRECTORY_SEPARATOR.'onPHP'.DIRECTORY_SEPARATOR
		);
	
	if (!defined('ONPHP_CLASS_CACHE'))
		define('ONPHP_CLASS_CACHE', ONPHP_TEMP_PATH);
	
	// classes autoload magic
	if (defined('ONPHP_CLASS_CACHE') && ONPHP_CLASS_CACHE) {
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
					
					foreach (
						glob($cache[$dirCount].'*'.EXT_CLASS, GLOB_NOSORT)
						as $class
					) {
						$class = basename($class, EXT_CLASS);
						
						// emulating include_path searching behaviour
						if (!isset($cache[$class]))
							$cache[$class] = $dirCount;
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
	} else {
		/* void */ function __autoload($classname)
		{
			if (strpos($classname, "\0") !== false) {
				/* are you sane? */
				return;
			}
			
			try {
				include $classname.EXT_CLASS;
				return /* void */;
			} catch (BaseException $e) {
				return __autoload_failed($classname, $e->getMessage());
			}
		}
	}
	
	// system settings
	error_reporting(E_ALL | E_STRICT);
	set_error_handler('error2Exception', E_ALL | E_STRICT);
	ignore_user_abort(true);
	define('ONPHP_VERSION', '1.0.6');
	
	if (!defined('ONPHP_IPC_PERMS'))
		define('ONPHP_IPC_PERMS', 0660);
	
	// paths
	define('ONPHP_ROOT_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('ONPHP_CORE_PATH', ONPHP_ROOT_PATH.'core'.DIRECTORY_SEPARATOR);
	define('ONPHP_MAIN_PATH', ONPHP_ROOT_PATH.'main'.DIRECTORY_SEPARATOR);
	define('ONPHP_META_PATH', ONPHP_ROOT_PATH.'meta'.DIRECTORY_SEPARATOR);
	
	if (!defined('ONPHP_META_PATH'))
		define(
			'ONPHP_META_PATH',
			ONPHP_ROOT_PATH.'meta'.DIRECTORY_SEPARATOR
		);
	
	define('ONPHP_META_CLASSES', ONPHP_META_PATH.'classes'.DIRECTORY_SEPARATOR);
	
	define(
		'ONPHP_INCUBATOR_PATH',
		ONPHP_ROOT_PATH.'incubator'.DIRECTORY_SEPARATOR
	);
	
	set_include_path(
		// current path
		get_include_path().PATH_SEPARATOR
		
		// core classes
		.ONPHP_CORE_PATH.'Base'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Cache'		.PATH_SEPARATOR
		
		.ONPHP_CORE_PATH.'DB'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'DB'.DIRECTORY_SEPARATOR.'Transaction'.PATH_SEPARATOR
		
		.ONPHP_CORE_PATH.'Exceptions'	.PATH_SEPARATOR
		
		.ONPHP_CORE_PATH.'Form'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Form'.DIRECTORY_SEPARATOR.'Filters'.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Form'.DIRECTORY_SEPARATOR.'Primitives'.PATH_SEPARATOR
		
		.ONPHP_CORE_PATH.'Logic'		.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'OSQL'			.PATH_SEPARATOR
		
		// main framework
		.ONPHP_MAIN_PATH.'Base'			.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Criteria'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Criteria'.DIRECTORY_SEPARATOR.'Projections'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Crypto'		.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'DAOs'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'DAOs'.DIRECTORY_SEPARATOR.'Handlers'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'DAOs'.DIRECTORY_SEPARATOR.'Workers'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Flow'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'SPL'			.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Net'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Net'.DIRECTORY_SEPARATOR.'Http'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Net'.DIRECTORY_SEPARATOR.'Mail'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Net'.DIRECTORY_SEPARATOR.'Ip'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Net'.DIRECTORY_SEPARATOR.'Soap'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Math'			.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Markup'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Markup'.DIRECTORY_SEPARATOR.'Feed'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Markup'.DIRECTORY_SEPARATOR.'Html'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'OpenId'		.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'EntityProto'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'EntityProto'.DIRECTORY_SEPARATOR.'Builders'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'EntityProto'.DIRECTORY_SEPARATOR.'Accessors'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'UnifiedContainer'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Utils'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'TuringTest'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'Archivers'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'IO'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'Logging'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Application'	.PATH_SEPARATOR
		
		.ONPHP_META_CLASSES.PATH_SEPARATOR
		
	/*
		.ONPHP_INCUBATOR_PATH
		.'classes'.DIRECTORY_SEPARATOR
		.'Application'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
			
		.ONPHP_INCUBATOR_PATH
		.'classes'.DIRECTORY_SEPARATOR
		.'Application'.DIRECTORY_SEPARATOR
		.'Markups'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
		
		.ONPHP_INCUBATOR_PATH
		.'classes'.DIRECTORY_SEPARATOR
		.'Application'.DIRECTORY_SEPARATOR
		.'Markups'.DIRECTORY_SEPARATOR
		.'Documents'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
	*/
	);
	
	define('ONPHP_CLASS_CACHE_CHECKSUM', '__occc');
	
	// file extensions
	define('EXT_CLASS', '.class.php');
	define('EXT_TPL', '.tpl.html');
	define('EXT_MOD', '.inc.php');
	define('EXT_HTML', '.html');
	define('EXT_UNIT', '.unit.php');
?>