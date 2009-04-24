<?php
	/* $Id$ */
	
	define('ONPHP_TEST_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	
	require ONPHP_TEST_PATH.'../global.inc.php.tpl';
	
	define('ENCODING', 'UTF-8');
	
	mb_internal_encoding(ENCODING);
	mb_regex_encoding(ENCODING);
	
	set_include_path(
		// current path
		get_include_path().PATH_SEPARATOR
		.ONPHP_TEST_PATH.'misc'.PATH_SEPARATOR
	);
	
	$testPathes = array(
		ONPHP_TEST_PATH.'core'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Ip'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR,
		ONPHP_TEST_PATH.'main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'Routers'.DIRECTORY_SEPARATOR
	);
	
	$dbs = array(
		'PgSQL' => array(
			'user'	=> 'onphp',
			'pass'	=> null,
			'host'	=> 'localhost',
			'base'	=> 'onphp'
		)
	);
	
	$daoWorkers = array(
		'NullDaoWorker', 'CommonDaoWorker', 'SmartDaoWorker', 'VoodooDaoWorker',
		'CacheDaoWorker', 'VoodooDaoWorker', 'SmartDaoWorker', 'CommonDaoWorker', 'NullDaoWorker'
	);
	
	VoodooDaoWorker::setDefaultHandler('CacheSegmentHandler');
	
	define('__LOCAL_DEBUG__', true);
?>