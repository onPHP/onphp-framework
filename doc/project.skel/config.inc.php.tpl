<?php
	// copy this file to 'config.inc.php' for local customization

	// system settings
	error_reporting(E_ALL | E_STRICT);
	setlocale(LC_CTYPE, "ru_RU.UTF8");
	setlocale(LC_TIME, "ru_RU.UTF8");
	
	if (!defined('PATH_SOURCE_DIR'))
		// defaults to user mode
		define('PATH_SOURCE_DIR', 'user'.DIRECTORY_SEPARATOR);

	// paths
	define('PATH_BASE', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('PATH_SOURCE', PATH_BASE.'src'.DIRECTORY_SEPARATOR.PATH_SOURCE_DIR);
	define('PATH_WEB', 'http://path/to/src/user/htdocs/');
	define('PATH_WEB_ADMIN', 'http://path/to/src/admin/htdocs/');
	define('PATH_WEB_PIX', '/pix/'); // dynamic stuff
	define('PATH_WEB_IMG', '/img/'); // static stuff
	
	// shared classes
	define(
		'PATH_CLASSES',
		PATH_BASE.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR
	);
	define('PATH_CONTROLLERS', PATH_SOURCE.'controllers'.DIRECTORY_SEPARATOR);
	define('PATH_TEMPLATES', PATH_SOURCE.'templates'.DIRECTORY_SEPARATOR);

	// onPHP init
	require '/path/to/onPHP/global.inc.php.tpl';
	
	// everything else
	define('DEFAULT_ENCODING', 'UTF-8');
	mb_internal_encoding(DEFAULT_ENCODING);
	mb_regex_encoding(DEFAULT_ENCODING);
	
	DBPool::me()->setDefault(
		DB::spawn('PgSQL', 'userName', 'passWord', 'hostName', 'baseName')->
		setEncoding(DEFAULT_ENCODING)
	);
	
	ini_set(
		'include_path', get_include_path().PATH_SEPARATOR
		.PATH_CLASSES.PATH_SEPARATOR
		.PATH_CONTROLLERS.PATH_SEPARATOR
		.PATH_CLASSES.'DAOs'.PATH_SEPARATOR
		.PATH_CLASSES.'Flow'.PATH_SEPARATOR
		.PATH_CLASSES.'Business'.PATH_SEPARATOR
		.PATH_CLASSES.'Proto'.PATH_SEPARATOR
		
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Business'.PATH_SEPARATOR
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Proto'.PATH_SEPARATOR
		.PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'DAOs'.PATH_SEPARATOR
	);
	
	// magic_quotes_gpc must be off
	
	define('__LOCAL_DEBUG__', true);
	define('BUGLOVERS', 'some.box@host.domain');

	Cache::setPeer(
		Memcached::create('localhost')
	);
	
	Cache::setDefaultWorker('SmartDaoWorker');
?>