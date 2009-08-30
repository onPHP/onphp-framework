<?php
	define('ONPHP_TEST_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	
	require ONPHP_TEST_PATH.'../global.inc.php.tpl';
	
	error_reporting(E_ALL); // & ~E_STRICT due to simpletest limitations
	
	set_include_path(
		// current path
		get_include_path().PATH_SEPARATOR
		.ONPHP_TEST_PATH.'core'.PATH_SEPARATOR
		.ONPHP_TEST_PATH.'base'.PATH_SEPARATOR
		.ONPHP_TEST_PATH.'misc'.PATH_SEPARATOR
	);
	
	$dbs = array(
/*
		'PgSQL' => array(
			'user'	=> 'onphp',
			'pass'	=> null,
			'host'	=> 'localhost',
			'base'	=> 'onphp'
		)
*/
	);
	
	define('SIMPLETEST_PATH', '/usr/share/php/simpletest/');
	
	define('__LOCAL_DEBUG__', true);
	
	// avoid E_STRICT, because SimpleTest doesn't support it
	set_error_handler('error2Exception', E_ALL);
	
	require SIMPLETEST_PATH.'unit_tester.php';
?>