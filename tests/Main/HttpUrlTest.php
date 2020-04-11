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
	
namespace OnPHP\Tests\Main;

use OnPHP\Main\Net\HttpUrl;
use OnPHP\Tests\TestEnvironment\TestCase;

final class HttpUrlTest extends TestCase
{
	private $urlWithPrivilegedPort = "https://path.to.some.com:444/hey.html";

	/**
	 * @test
	 */
	public function privilegedPortsValidationEnabled()
	{
		$url =
			HttpUrl::create()->
				parse($this->urlWithPrivilegedPort)->
				setCheckPrivilegedPorts();

		$this->assertFalse($url->isValid());
		$this->assertTrue($url->isPrivilegedPortUsed());
	}

	/**
	 * @test
	 */
	public function privilegdPortsValidationDisabled()
	{
		$url = HttpUrl::create()->parse($this->urlWithPrivilegedPort);

		$this->assertTrue($url->isValid());
		$this->assertTrue($url->isPrivilegedPortUsed());
	}

}
?>