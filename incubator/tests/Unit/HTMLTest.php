<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Test for test case
	**/
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.inc.php';
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Framework/TestCase.php';
    import('Unit');

	class HTMLTest_Test extends PHPUnit2_Framework_TestCase
	{
        public $test;
        public $result;
        
        function setUP() {
            $this->test = new HTMLTest('login=Sveta&password=secret', 'login: Sveta<br> password: secret', 'url', 'post');
            $this->result = <<<RES
            login: Sveta<br>
            password: secret
RES;
        }
        
		function test_handle()
		{
			$this->assertEquals($this->test->getExpected(), $this->test->handle($this->result));
		}

	}

	$suite = new PHPUnit2_Framework_TestSuite('HTMLTest_Test');
	$result = $suite->run();
	$message = '';

	if (!$result->wasSuccessful() && 0 < $result->failureCount()) {
		$message .= "<h1>Attention! Some tests failed!</h1>\n";
		$message .= var_export($result->failureCount(), true);
	} else {
		$message .= "<h1>All tests passed!</h1>\n";
	}
	
	if (!isset($_SERVER['HTTP_ACCEPT']))
		$message = strip_tags($message);

	echo $message;
?>