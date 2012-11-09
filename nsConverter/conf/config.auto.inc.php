<?php
// system settings
namespace Onphp\NsConverter;

error_reporting(E_ALL | E_STRICT);

setlocale(LC_CTYPE, "en_US.UTF8");
setlocale(LC_TIME, "en_US.UTF8");

// Xdebug settings @see phpinfo()
ini_set('display_errors', 1);
ini_set('xdebug.show_local_vars', 'on');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.GET', '*');
ini_set('xdebug.collect_params', 'on');
ini_set('xdebug.var_display_max_depth', 8);
ini_set('xdebug.var_display_max_data', 4096);
date_default_timezone_set('Europe/Moscow');

//including project constants
if (file_exists('constants.inc.php'))
	require_once('constants.inc.php');
else
	require_once('constants.inc.tpl.php');

//Project constants
define('PATH_BASE', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('PATH_SRC', PATH_BASE.'src'.DIRECTORY_SEPARATOR);

//SRC PATCHES
define('PATH_CLASSES', PATH_SRC);

// everything else
define('DEFAULT_ENCODING', 'UTF-8');
mb_internal_encoding(DEFAULT_ENCODING);
mb_regex_encoding(DEFAULT_ENCODING);

//including onPHP:
require dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'global.inc.php.tpl';

//including project classes
\Onphp\AutoloaderClassPathCache::create()
	->setNamespaceResolver(\Onphp\NamespaceResolverPSR0::create())
	->addPaths([
		PATH_SRC,
		PATH_SRC.'Auto'.DIRECTORY_SEPARATOR.'Business',
		PATH_SRC.'Auto'.DIRECTORY_SEPARATOR.'Proto',
		PATH_SRC.'Auto'.DIRECTORY_SEPARATOR.'DAOs',

		PATH_SRC.'AddUtils',
		PATH_SRC.'Buffers',
		PATH_SRC.'Business',
		PATH_SRC.'DAOs',
		PATH_SRC.'Flow',
		PATH_SRC.'Proto',
		PATH_SRC.'Utils',
	], 'Onphp\NsConverter')
	->register();

if (file_exists('config.inc.php'))
	require 'config.inc.php';
else
	require 'config.inc.tpl.php';
