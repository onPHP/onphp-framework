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
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
                                    . DIRECTORY_SEPARATOR . 'global.inc.php';
	error_reporting(E_ALL);
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Framework/TestCase.php';

	class PropertyFilterForm_Test extends PHPUnit2_Framework_TestCase
	{
        public $setting;
        
        function setUP()
        {
            $this->setting = Singletone::getInstance()->PropertyFilterForm();
            $this->setting->setDefaultHandler('htmlspecialchars');
        }
        
		function test_Area()
		{
			$this->assertEquals('?=test', $this->setting->getArea('test'));    //    may be handle it later???
            $this->setting->setArea('http://www.test.ru/test.xxx', 'area');
			$this->assertEquals('http://www.test.ru/test.xxx?area=test', $this->setting->getArea('test'));
			
		}

		function test_Form()
		{
            $form = new Form();
            $this->setting->setForm(
                 $form->add(Primitive::string('test1')->
						   setDefault('')->
                           setValue('<test>')->
                           setRawValue('<test>')->
						   setRequired(true))->
		 				add(Primitive::string('test2')->
						   setDefault('')->
                           setRawValue('')->
						   setRequired(true)));
			$this->assertEquals('<test>', $this->setting->getRawValue('test1'));
			$this->assertEquals('', $this->setting->getRawValue('test2'));
            $this->assertEquals('&lt;test&gt;', $this->setting->getValue('test1'));
			$this->assertEquals(null, $this->setting->getValue('test2'));
		}

	}

	$suite = new PHPUnit2_Framework_TestSuite('PropertyFilterForm_Test');
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