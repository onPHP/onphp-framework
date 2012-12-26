<?php

	if (!extension_loaded('onphp'))
		echo 'Trying to load onPHP extension.. '
			.(@dl('onphp.so') ? 'done.' : 'failed!') .PHP_EOL;

	date_default_timezone_set('Europe/Moscow');
	define('ONPHP_TEST_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	require ONPHP_TEST_PATH.'../global.inc.php.tpl';

	define('ENCODING', 'UTF-8');

	mb_internal_encoding(ENCODING);
	mb_regex_encoding(ENCODING);

	AutoloaderPool::get('onPHP')->addPath(ONPHP_TEST_PATH.'misc');

	$testPathes = array(
		ONPHP_TEST_PATH.'core'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Autoloader'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Ip'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'Routers'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'AMQP'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'db'.DIRECTORY_SEPARATOR,
	);

	$dbs = array(
		'PgSQL' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'MySQL' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'SQLitePDO' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
	);

	$daoWorkers = array(
		'NullDaoWorker', 'CommonDaoWorker', 'SmartDaoWorker', 'VoodooDaoWorker',
		'CacheDaoWorker', 'VoodooDaoWorker', 'SmartDaoWorker', 'CommonDaoWorker', 'NullDaoWorker'
	);
	
	VoodooDaoWorker::setDefaultHandler('CacheSegmentHandler');
	
	define('__LOCAL_DEBUG__', true);
	define('ONPHP_CURL_TEST_URL', 'http://localhost/curlTest.php'); //set here url to test script test/main/data/curlTest/curlTest.php
