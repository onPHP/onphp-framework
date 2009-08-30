<?php
	// copy this file to 'config.inc.php' for local customization
	
	// system settings
	error_reporting(E_ALL | E_STRICT);
	setlocale(LC_CTYPE, "ru_RU.UTF8");
	setlocale(LC_TIME, "ru_RU.UTF8");
	
	// paths
	define('PATH_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	define('PATH_WEB', 'http://path/to/src/www/');
	define('PATH_WEB_PIX', '/pix/'); // dynamic stuff
	define('PATH_WEB_IMG', '/img/'); // static stuff
	define('PATH_WEB_ADMIN', 'http://path/to/src/admin/');
	
	define('PATH_CLASSES', PATH_BASE.'classes'.DIRECTORY_SEPARATOR);
	define('PATH_CONTROLLERS', PATH_BASE.'controllers'.DIRECTORY_SEPARATOR);
	define('PATH_TEMPLATES', PATH_BASE.'templates'.DIRECTORY_SEPARATOR);
	
	// onPHP init
	require realpath('/path/to/onPHP/global.inc.php.tpl');
	
	// default db settings
	define('DB_BASE', 'baseName');
	define('DB_USER', 'userName');
	define('DB_PASS', 'userPassword');
	define('DB_HOST', 'localhost');
	define('DB_CLASS', 'PgSQL');
	
	// everything else
	define('DEFAULT_ENCODING', 'UTF8');
	mb_internal_encoding(DEFAULT_ENCODING);
	mb_regex_encoding(DEFAULT_ENCODING);

	ini_set(
		'include_path', get_include_path().PATH_SEPARATOR
		.PATH_CLASSES.PATH_SEPARATOR
		.PATH_CLASSES.'DAOs'.PATH_SEPARATOR
		.PATH_CLASSES.'Flow'.PATH_SEPARATOR
		.PATH_CLASSES.'Business'.PATH_SEPARATOR
		
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Business'.PATH_SEPARATOR
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Proto'.PATH_SEPARATOR
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'DAOs'.PATH_SEPARATOR
	);
	
	// magic_quotes_gpc must be off
	
	define('__LOCAL_DEBUG__', true);
	define('BUGLOVERS', 'some.box@host.domain');

	Cache::setPeer(
		new ReferencePool(
			Memcached::create('localhost')
		)
	);
	
	Cache::setDefaultWorker('SmartDaoWorker');
?>