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
	 * Test for import function
	**/
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Framework/TestCase.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.inc.php';

	class import_Test extends PHPUnit2_Framework_TestCase
	{
		function test_import()
		{
			$this->assertTrue(import('Form'));
			$this->assertTrue(import('Form.Form'));
			$this->assertType('BaseException', import('NotExisting'));
			$this->assertType('BaseException', import('NotExisting.BadName'));
		}

		function test_autoload()
		{
			$this->assertType('Primitive', new Primitive);
		}

		function test_instance()
		{
			$this->assertEquals(new Form, instance('Form.Form'));
		}
	}

	$suite = new PHPUnit2_Framework_TestSuite('import_Test');
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