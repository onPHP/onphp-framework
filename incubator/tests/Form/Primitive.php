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
    import('Form');

	class Primitive_Test extends PHPUnit2_Framework_TestCase
	{
        function &get_ref($array) {
            return $array;
        }
		function test_email()
		{
			$email = Primitive::email('name');
            $this->assertTrue($email->import($this->get_ref(array('name' => 'sveta@programming.microbecal.com'))));
            $this->assertTrue($email->import($this->get_ref(array('name' => '<Света Смирнова> sveta.smirnova@microbecal.com'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => 'sveta@microbecal.c'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => 'sveta @microbecal.com'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => '@microbecal.com'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => 'sveta@micro$becal.com'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => 'sveta@microbecal.com.programming'))));
            $this->assertFalse($email->import($this->get_ref(array('name' => 'sveta.microbecal.com'))));
		}

		function test_password()
		{
			$pass = Primitive::password('name');
            $this->assertTrue($pass->import($this->get_ref(array('name' => 'ds&^87d-fds'))));
            $this->assertTrue($pass->import($this->get_ref(array('name' => 'FDF`jssd'))));
            $this->assertFalse($pass->import($this->get_ref(array('name' => 'short'))));
            $this->assertFalse($pass->import($this->get_ref(array('name' => '   '))));
            $pass->setMax(10);
            $this->assertFalse($pass->import($this->get_ref(array('name' => 'long-longstring'))));
		}

		function test_nick()
		{
			$nick = Primitive::nick('name');
            //$this->assertTrue($nick->import($this->get_ref(array('name' => 'Света Смирнова'))));
            $this->assertTrue($nick->import($this->get_ref(array('name' => 'Sveta Smirnova'))));
            $this->assertTrue($nick->import($this->get_ref(array('name' => 'svetasmirnova'))));
            $this->assertFalse($nick->import($this->get_ref(array('name' => 'a'))));
            $this->assertFalse($nick->import($this->get_ref(array('name' => 'sveta-smirnova'))));
		}

		function test_url ()
		{
			$url = Primitive::url('url');
			$this->assertEquals('http://', $url->getDefaultProtocol());
			$this->assertEquals(array('http', 'ftp', 'https', 'mailto', 'gopher'), $url->getAlloweds());
			$this->assertFalse($url->import($this->get_ref(array('url' => 'http://some invalid'))));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'unknown:some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'mailto:sveta@microbecal.com'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'ftp://some.valid.host'))));
			$url->setAlloweds(array('http', 'https'));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'mailto:sveta@microbecal.com'))));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'ftp://some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'http://some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'localhost'))));
		}
		
		function test_host ()
		{
			$url = Primitive::host('url');
			$this->assertEquals('http://', $url->getDefaultProtocol());
			$this->assertEquals(array('http', 'ftp', 'https'), $url->getAlloweds());
			$this->assertFalse($url->import($this->get_ref(array('url' => 'http://some invalid'))));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'unknown:some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'ftp://some.valid.host'))));
			$url->setAlloweds(array('http', 'https'));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'mailto:sveta@microbecal.com'))));
			$this->assertFalse($url->import($this->get_ref(array('url' => 'ftp://some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'http://some.valid.host'))));
			$this->assertTrue($url->import($this->get_ref(array('url' => 'localhost'))));
		}
		
	}

	$suite = new PHPUnit2_Framework_TestSuite('Primitive_Test');
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