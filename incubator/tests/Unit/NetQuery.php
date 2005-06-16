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
    //import('Unit');

	class NetQuery_Test extends PHPUnit2_Framework_TestCase
	{
        public $nquery;
        public $url;
        public $post_query;
        public $post_expected;
        public $get_query;
        public $get_expected;
        
        function setUP()
        {
            $this->nquery           = new NetQuery(WWW);
            $this->url              = TEST_URL . INDEX;
            $this->get_query        = 'name=Sveta&password=secret';
            $this->post_query       = 'name=Sveta%20Smirnova&password=secret';
            $this->post_expected    = '$POST[name] = Sveta Smirnova<br>$POST[password] = secret<br>';
            $this->get_expected     = '$GET[name] = Sveta<br>$GET[password] = secret<br>';
        }
        
		function test_get()
		{
			$this->assertTrue(Suite::equals($this->get_expected, $this->nquery->query($this->get_query, $this->url)));
			$this->assertFalse(Suite::equals('not expected string', $this->nquery->query($this->get_query, $this->url)));
		}
        
		function test_post()
		{
			$this->assertTrue(Suite::equals($this->post_expected, $this->nquery->query($this->post_query, $this->url, 'post')));
			$this->assertFalse(Suite::equals('not expected string', $this->nquery->query($this->post_query, $this->url, 'post')));
		}

		function test_session()
		{
            //  todo!!!
		}

	}

	$suite = new PHPUnit2_Framework_TestSuite('NetQuery_Test');
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
