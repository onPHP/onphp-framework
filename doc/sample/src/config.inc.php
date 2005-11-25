<?php
	/* $Id$ */

	// system settings
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_erorrs', true);
	setlocale(LC_CTYPE, "ru_RU.UTF8");
	setlocale(LC_TIME, "ru_RU.UTF8");
	date_default_timezone_set('Europe/Moscow');

	// paths
	define('PATH_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	define('PATH_WEB', 'http://localhost/php/onPHP/doc/sample/src/www/');
	define('PATH_CLASSES', PATH_BASE.'classes'.DIRECTORY_SEPARATOR);
	define('PATH_MODULES', PATH_BASE.'modules'.DIRECTORY_SEPARATOR);
	define('PATH_TEMPLATES', PATH_BASE.'templates'.DIRECTORY_SEPARATOR);
	
	// onPHP
	require realpath(PATH_BASE.'../../../global.inc.php.tpl');
	
	// db settings
	define('DB_BASE', '/home/voxus/bases/gb');
	define('DB_USER', 'voxus');
	define('DB_PASS', '12345');
	define('DB_HOST', 'localhost');
	define('DB_CLASS', 'IBase'); // or: PgSQL, MySQL, OraSQL

	// everything else
	define('DEFAULT_ENCODING', 'UTF8');

	ini_set(
		'include_path', get_include_path().PATH_SEPARATOR.
		PATH_CLASSES.PATH_SEPARATOR.
		PATH_CLASSES.'DAOs'.PATH_SEPARATOR.
		PATH_CLASSES.'Base'.PATH_SEPARATOR.
		PATH_CLASSES.'Business'.PATH_SEPARATOR
	);
	
	// magic_quotes_gpc must be off

	Cache::setPeer(
		AggregateCache::create()->addPeer('local', Memcached::create())
	);
?>
