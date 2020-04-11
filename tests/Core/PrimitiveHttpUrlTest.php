<?php
/***************************************************************************
 *   Copyright (C) 2013 by Vyacheslav Yu. Tsyrulnik                        *
*                                                                         *
*   This program is free software; you can redistribute it and/or modify  *
*   it under the terms of the GNU Lesser General Public License as        *
*   published by the Free Software Foundation; either version 3 of the    *
*   License, or (at your option) any later version.                       *
*                                                                         *
***************************************************************************/

namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitive;
use OnPHP\Tests\TestEnvironment\TestCase;

final class PrimitiveHttpUrlTest extends TestCase
{
	private $urlWithPrivilegedPort = "https://path.to.some.com:444/hey.html";

	/**
	 * @test
	 */
	public function privilegedPortIsOkByDefault()
	{
		$form = Form::create()->add(Primitive::httpUrl("url"));

		$form->import(array('url' => $this->urlWithPrivilegedPort));
		$errors = $form->getErrors();

		$this->assertFalse(isset($errors["url"]));
	}

	/**
	 * @test
	 */
	public function privilegedPortInvalid()
	{
		$form = Form::create()->add(Primitive::httpUrl("url")->setCheckPrivilegedPorts());

		$form->import(array('url' => $this->urlWithPrivilegedPort));
		$errors = $form->getErrors();

		$this->assertTrue(isset($errors["url"]));
		$this->assertEquals(Form::WRONG, $errors["url"]);
	}

}
?>