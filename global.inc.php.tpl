<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	// sample system-wide configuration file
	
	function error2Exception($code, $string, $file, $line, $context)
	{
		throw new BaseException($string, $code);
	}
	
	// classes autoload magic
	/* void */ function __autoload($classname)
	{
		// numeric indexes for directories, literal indexes for classes
		static $cache = array();
		
		if (defined('ONPHP_CLASS_CACHE') && ONPHP_CLASS_CACHE) {
			if (!$cache && is_readable(ONPHP_CLASS_CACHE))
				$cache = unserialize(file_get_contents(ONPHP_CLASS_CACHE, false));
		} else {
			// cache is disabled
			require $classname.EXT_CLASS;
			return /* void */;
		}
		
		$length = strlen(get_include_path());
		
		if (
			!isset($cache[ONPHP_CLASS_CACHE_CHECKSUM])
			|| ($cache[ONPHP_CLASS_CACHE_CHECKSUM] <> $length)
		) {
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
			
			$cache[ONPHP_CLASS_CACHE_CHECKSUM] = $length;
			
			if (
				is_writable(dirname(ONPHP_CLASS_CACHE))
				&& (
					!file_exists(ONPHP_CLASS_CACHE)
					|| is_writable(ONPHP_CLASS_CACHE)
				)
			)
				file_put_contents(ONPHP_CLASS_CACHE, serialize($cache));
		}
		
		if (isset($cache[$classname])) {
			require $cache[$cache[$classname]].$classname.EXT_CLASS;
		} else {
			// ok, last chance to find class in non-cached include_path
			try {
				include $classname.EXT_CLASS;
				$cache[ONPHP_CLASS_CACHE_CHECKSUM] = null;
				return /* void */;
			} catch (BaseException $e) {
				eval(
					'class '.$classname.'{/*_*/}'
					.'if (!class_exists("ClassNotFoundException", false)) { '
					.'class ClassNotFoundException extends BaseException {/*_*/} }'
					.'throw new ClassNotFoundException("'.$classname.'");'
				);
			}
		}
	}
	
	// system settings
	error_reporting(E_ALL | E_STRICT);
	set_error_handler('error2Exception', E_ALL | E_STRICT);
	ignore_user_abort(true);
	define('ONPHP_VERSION', '0.10.0.99');

	// overridable constant, don't forget for trailing slash
	// also you may consider using /dev/shm/ for cache purposes
	if (!defined('ONPHP_TEMP_PATH'))
		define('ONPHP_TEMP_PATH', '/tmp/onPHP/');
	
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
		ONPHP_ROOT_PATH
			.'incubator'
			.DIRECTORY_SEPARATOR
			.'classes'
			.DIRECTORY_SEPARATOR
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
		
		.ONPHP_MAIN_PATH.'DAOs'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'DAOs'.DIRECTORY_SEPARATOR.'Handlers'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'DAOs'.DIRECTORY_SEPARATOR.'Workers'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Flow'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'SPL'			.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'UnifiedContainer'.PATH_SEPARATOR
		
		.ONPHP_MAIN_PATH.'Mail'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'TuringTest'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'.DIRECTORY_SEPARATOR.'Archivers'.PATH_SEPARATOR
		
		.ONPHP_META_CLASSES.PATH_SEPARATOR
		
	/*
		.ONPHP_INCUBATOR_PATH.'Application'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
			
		.ONPHP_INCUBATOR_PATH.'Application'.DIRECTORY_SEPARATOR
		.'Markups'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
		
		.ONPHP_INCUBATOR_PATH.'Application'.DIRECTORY_SEPARATOR
		.'Markups'.DIRECTORY_SEPARATOR
		.'Documents'.DIRECTORY_SEPARATOR.PATH_SEPARATOR
	*/
		// uncomment this one, if your php compiled without spl extension:
		//
		// .ONPHP_CORE_PATH.'SPL'.DIRECTORY_SEPARATOR.'bundled'.PATH_SEPARATOR
	);
	
	/*
		if (!defined('ONPHP_CLASS_CACHE') && defined('PATH_BASE'))
			define('ONPHP_CLASS_CACHE', PATH_BASE.'class.cache');
	*/
	
	define('ONPHP_CLASS_CACHE_CHECKSUM', '__occc');
	
	// file extensions
	define('EXT_CLASS', '.class.php');
	define('EXT_TPL', '.tpl.html');
	define('EXT_MOD', '.inc.php');
	define('EXT_HTML', '.html');
	define('EXT_UNIT', '.unit.php');
?>