<?php
	error_reporting(E_ALL | E_STRICT);
	
	setlocale(LC_CTYPE, "ru_RU.UTF8");
	setlocale(LC_TIME, "ru_RU.UTF8");
	
	define('DEFAULT_ENCODING', 'UTF8');
	mb_internal_encoding(DEFAULT_ENCODING);
	
	define('PATH_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	
	// used by meta
	define(
		'PATH_CLASSES',
		PATH_BASE.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR
	);

	require '/var/www/libs/onphp-0.10/global.inc.php';

	Application::init(CommonApplication::create());

	Application::me()->setName('project');
	
	session_save_path('/tmp/sess/'.Application::me()->getName());

	define('CFG_HOST_BASE_PATH', '/var/www/libs/onphp-0.10/incubator/olap.test/cfg/');

	HostConfiguration::me()->setup(
		PATH_BASE, CFG_HOST_BASE_PATH,
		HostConfiguration::LOCAL, 'simple'
	);

	HostConfiguration::me()->includeConfig('packages');

	// paths settings
	Application::me()->setPathResolver(
		new PathResolver(
			PATH_BASE.'src'.DIRECTORY_SEPARATOR,

			PackageConfiguration::createApplicationPaths()->
			addClassPath('ViewHelpers')
		)
	);

	// url settings
	Application::me()->setLocations(
		CommonLocationSettings::create()->
		setWeb(
			//TODO: Use main/Application/ApplicationUrl
			AppUrl::create()->
			setUrl('http://www.myproject.com/')->
			setNavigationSchema(SimpleNavigationSchema::create())
		)->
		setAdmin(
			//TODO: Use main/Application/ApplicationUrl
			AppUrl::create()->
			setUrl('https://myproject.com/')->
			setNavigationSchema(SimpleNavigationSchema::create())
		)
	);
	
	// uncomment this on test/localhost
	define('DB_USER', 'user');
	define('DB_BASE', Application::me()->getName());

	// use includeConfig() on test/localhost
	HostConfiguration::me()->includeConfig('db');

	// magic_quotes_gpc must be off
	
	// debug settings
	define('__LOCAL_DEBUG__', true);
	define('BUGLOVERS', 'some.box@host.domain');

	// use includeConfig() on test/localhost
	HostConfiguration::me()->includeConfig('cache');

	// Cache::me()->clean();
?>