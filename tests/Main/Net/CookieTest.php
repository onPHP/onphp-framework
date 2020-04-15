<?php

namespace OnPHP\Tests\Main\Net;

use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Main\Net\Http\Cookie;
use OnPHP\Main\Net\Http\CookieCollection;
use OnPHP\Tests\TestEnvironment\TestCase;

final class CookieTest extends TestCase
{
	public function testCookie()
	{
		if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
			$this->markTestSkipped('can\'t test cookies without web');

		echo "\0";

		$this->expectException(WrongStateException::class);
		
		Cookie::create('testCookie')->
			setValue('testValue')->
			setMaxAge(60*60)->
			httpSet();
	}

	public function testCookieCollection()
	{
		if (!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
			$this->markTestSkipped('can\'t test cookies without web');

		echo "\0";

		$this->expectException(WrongStateException::class);
		
		CookieCollection::create()->
			add(
				Cookie::create('anotherTestCookie')->
					setValue('testValue')->
					setMaxAge(60*60)
			)->
			httpSetAll();
	}
}
?>