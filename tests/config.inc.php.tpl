<?php

use OnPHP\Core\DB\MySQLim;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\DB\SQLitePDO;
use OnPHP\Main\DAO\Handlers\CacheSegmentHandler;
use OnPHP\Main\DAO\Worker\CacheDaoWorker;
use OnPHP\Main\DAO\Worker\CommonDaoWorker;
use OnPHP\Main\DAO\Worker\NullDaoWorker;
use OnPHP\Main\DAO\Worker\SmartDaoWorker;
use OnPHP\Main\DAO\Worker\VoodooDaoWorker;

	$dbs = array(
		PgSQL::class => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		MySQLim::class => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
		SQLitePDO::class => array(
			'user'	=> 'onphp',
			'pass'	=> 'onphp',
			'host'	=> '127.0.0.1',
			'base'	=> 'onphp'
		),
	);

$daoWorkers = array(
	VoodooDaoWorker::class,
	CommonDaoWorker::class,
	SmartDaoWorker::class,
	VoodooDaoWorker::class,
	CacheDaoWorker::class,
	VoodooDaoWorker::class,
	SmartDaoWorker::class,
	CommonDaoWorker::class,
	NullDaoWorker::class
);

VoodooDaoWorker::setDefaultHandler(CacheSegmentHandler::class);

define('__LOCAL_DEBUG__', true);
define('ONPHP_CURL_TEST_URL', 'http://localhost/curlTest.php'); //set here url to test script test/main/data/curlTest/curlTest.php
?>