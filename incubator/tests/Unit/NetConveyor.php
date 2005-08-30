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

	class NetConveyor_Test extends PHPUnit2_Framework_TestCase
	{
        public $Conveyor;
        public $tests;
        
        function setUP()
        {
            $this->tests = array(
                          new HTMLTest('name=Sveta&password=secret',
                                       '$GET[name] = Sveta<br>$GET[password] = secret<br>',
                                       TEST_URL . INDEX),
                          new HTMLTest('name=Sveta%20Smirnova&password=secret',
                                       '$POST[name] = Sveta Smirnova<br>$POST[password] = secret<br>',
                                       TEST_URL . INDEX, 'post'),
                          );
            $this->Conveyor = new NetConveyor($this->tests, WWW);
        }
        
		function test_test()
		{
			$this->assertTrue($this->Conveyor->test());
            $this->tests[] = new HTMLTest('name=Sveta&password=secret',
                                       '$GET[name] = NoSveta<br>$GET[password] = secret<br>',
                                       TEST_URL . INDEX);
			$this->assertFalse($this->Conveyor->test());
		}
        
		function test_getSuccessfuls()
		{
            $this->Conveyor->test();
			$this->assertEquals(2, $this->Conveyor->getSuccessfuls());
		}
        
		function test_getFailures()
		{
            $this->Conveyor->test();
			$this->assertEquals(array(), $this->Conveyor->getFailures());
		}

	}

	$suite = new PHPUnit2_Framework_TestSuite('NetConveyor_Test');
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
