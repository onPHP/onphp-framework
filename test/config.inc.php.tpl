<?php
	
	$dbs = array(
		'\Onphp\PgSQL' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'\Onphp\MySQLim' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		'\Onphp\SQLitePDO' => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
	);

	$daoWorkers = array(
		'\Onphp\NullDaoWorker', '\Onphp\CommonDaoWorker', '\Onphp\SmartDaoWorker', '\Onphp\VoodooDaoWorker',
		'\Onphp\CacheDaoWorker', '\Onphp\VoodooDaoWorker', '\Onphp\SmartDaoWorker', '\Onphp\CommonDaoWorker', '\Onphp\NullDaoWorker'
	);
	
	\Onphp\VoodooDaoWorker::setDefaultHandler('\Onphp\CacheSegmentHandler');
	
	define('__LOCAL_DEBUG__', true);
	define('ONPHP_CURL_TEST_URL', 'http://localhost/curlTest.php'); //set here url to test script test/main/data/curlTest/curlTest.php
?>
