<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	// sample system-wide configuration file
    
	function error2Exception($code, $string, $file, $line)
	{
		throw new BaseException($string, $code, $file, $line);
	}

	//  __autoload
	function __autoload($classname)
	{
		// and yes, there is no error handling, 'cause we're
		// writing very custom business solution, which will
		// contain everything (classes/modules) everytime...
		require $classname . EXT_CLASS;
	}
    
	// system settings
	error_reporting(E_ALL | E_STRICT);
	set_error_handler ('error2Exception', E_ALL);
    
	// paths
	define('ONPHP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
	define('ONPHP_INCUBATOR_PATH',
		dirname(ONPHP_PATH) . DIRECTORY_SEPARATOR .
		'incubator' . DIRECTORY_SEPARATOR
	);
	define('ONPHP_CLASSES', ONPHP_PATH . 'classes' . DIRECTORY_SEPARATOR);
	set_include_path(
		ONPHP_CLASSES . 'Base' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Expression' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Logic' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'OSQL' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'DAOs' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Form' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Utils' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Template' . PATH_SEPARATOR .
		ONPHP_CLASSES . 'Module' . PATH_SEPARATOR .
		get_include_path() . PATH_SEPARATOR
	);
    
	// file extensions
	define('EXT_CLASS', '.class.php');
	define('EXT_TPL', '.tpl.html');
	define('EXT_MOD', '.inc.php');
	define('EXT_HTML', '.html');
?>
