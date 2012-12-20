<?php
	
	$dbs = array(
		'PgSQL' => array(
			'user'	=> 'postgres',
			'pass'	=> '',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'MySQL' => array(
			'user'	=> 'root',
			'pass'	=> '',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'SQLitePDO' => array(
			'user'	=> '',
			'pass'	=> '',
			'host'	=> '127.0.0.1',
			'base'	=> ':memory:'
		),
	);

	$daoWorkers = array(
		'NullDaoWorker', 'CommonDaoWorker', 'SmartDaoWorker', 'VoodooDaoWorker',
		'CacheDaoWorker', 'VoodooDaoWorker', 'SmartDaoWorker', 'CommonDaoWorker', 'NullDaoWorker'
	);

	VoodooDaoWorker::setDefaultHandler('CacheSegmentHandler');

	define('__LOCAL_DEBUG__', true);
	define('ONPHP_CURL_TEST_URL', 'http://localhost/curlTest.php'); //set here url to test script test/main/data/curlTest/curlTest.php
?>
