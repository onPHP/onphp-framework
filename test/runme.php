<?php
	/* $Id$ */
	
	$config = dirname(__FILE__).'/config.inc.php';
	
	require is_readable($config) ? $config : $config.'.tpl';
	
	$reporter = php_sapi_name() == 'cli' ? new TextReporter() : new HtmlReporter();
	
	$test = new GroupTest('onPHP-'.ONPHP_VERSION);
	
	foreach (glob(ONPHP_TEST_PATH.'{core,main}/*.class.php', GLOB_BRACE) as $file)
		$test->addTestFile($file);
	
	// meta, DB and DAOs ordered tests portion
	if (isset($dbs) && $dbs) {
		Singleton::getInstance('DBTestPool', $dbs);
		
		// build stuff from meta
		
		$metaDir = ONPHP_TEST_PATH.'meta'.DIRECTORY_SEPARATOR;
		
		$_SERVER['argv'][1] = $metaDir.'config.inc.php';
		
		$_SERVER['argv'][2] = $metaDir.'config.meta.xml';
		
		require ONPHP_META_PATH.'bin'.DIRECTORY_SEPARATOR.'build.php';
	}
	
	$test->run($reporter);
?>