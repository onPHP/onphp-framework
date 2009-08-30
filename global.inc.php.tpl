<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	// sample system-wide configuration file
	
	function error2Exception($code, $string, $file, $line)
	{
		throw new BaseException($string, $code, $file, $line);
	}
	
	// classes autoload magic
	function __autoload($classname)
	{
		if (strpos($classname, "\0") !== false) {
			/* are you sane? */
			return;
		}
		
		// and yes, there is no error handling, 'cause we're
		// writing very custom business solution, which will
		// contain everything (classes/modules) everytime...
		require $classname . EXT_CLASS;
	}
	
	// system settings
	error_reporting(E_ALL | E_STRICT);
	set_error_handler('error2Exception', E_ALL);
	ignore_user_abort(true);
	define('ONPHP_VERSION', '0.2.19.99');
	
	// overridable constant, don't forget for trailing slash
	// also you may consider using /dev/shm/ for cache purposes
	if (!defined('ONPHP_TEMP_PATH'))
		define('ONPHP_TEMP_PATH', '/tmp/onPHP/');
	
	// paths
	define('ONPHP_ROOT_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('ONPHP_CORE_PATH', ONPHP_ROOT_PATH.'core'.DIRECTORY_SEPARATOR);
	define('ONPHP_MAIN_PATH', ONPHP_ROOT_PATH.'main'.DIRECTORY_SEPARATOR);
	define('ONPHP_META_PATH', ONPHP_ROOT_PATH.'meta'.DIRECTORY_SEPARATOR);
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
		
		// to reduce include path - run incubator/misc/renewSymLinks.sh
		// and uncomment this two paths
		/**
		.ONPHP_CORE_PATH.'.all'.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'.all'.PATH_SEPARATOR
		**/
		
		// core classes
		.ONPHP_CORE_PATH.'Base'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Cache'		.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'DB'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Exceptions'	.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Form'			.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'Logic'		.PATH_SEPARATOR
		.ONPHP_CORE_PATH.'OSQL'			.PATH_SEPARATOR
		
		// main framework
		.ONPHP_MAIN_PATH.'Base'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'CMF'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'DAOs'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Flow'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Containers'	.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'HTTP'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Module'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Net'			.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Template'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'Utils'		.PATH_SEPARATOR
		.ONPHP_MAIN_PATH.'XML'			.PATH_SEPARATOR

		// incubator's stuff
		/**
		.ONPHP_INCUBATOR_PATH.'NetTools'			.PATH_SEPARATOR
		.ONPHP_INCUBATOR_PATH.'SimpleTestWrapper'	.PATH_SEPARATOR
		**/
	);
	
	// file extensions
	define('EXT_CLASS', '.class.php');
	define('EXT_TPL', '.tpl.html');
	define('EXT_MOD', '.inc.php');
	define('EXT_HTML', '.html');
	define('EXT_UNIT', '.unit.php');
?>