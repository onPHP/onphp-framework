<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main;

use OnPHP\Core\Exception\NetworkException;
use OnPHP\Main\Flow\HttpRequest;
use OnPHP\Main\Net\Http\CurlHttpClient;
use OnPHP\Main\Net\Http\HttpMethod;
use OnPHP\Main\Net\Http\HttpStatus;
use OnPHP\Main\Net\HttpUrl;
use OnPHP\Tests\TestEnvironment\TestCase;

final class HttpUtilsTest extends TestCase
{
	public function testCurlGet()
	{
		$request = HttpRequest::create()->
			setUrl(
				HttpUrl::create()->parse('https://github.com/')
			)->
			setHeaderVar('User-Agent', 'onphp-test')->
			setMethod(HttpMethod::get());

		try {
			$response = CurlHttpClient::create()->
				setTimeout(3)->
				send($request);
		} catch (NetworkException $e) {
			return $this->markTestSkipped('no network available');
		}

		$this->assertEquals(
			$response->getStatus()->getId(),
			HttpStatus::CODE_200
		);
		
		$this->assertStringContainsString(
			'github',
			$response->getBody()
		);
		
		$this->expectException(NetworkException::class);
		
		$badResponse = CurlHttpClient::create()->
				setTimeout(3)->
				setMaxFileSize(100)-> // github page is bigger than 100 bytes
				send($request);
	}

	public function testCurlException()
	{
		$request = HttpRequest::create()->
			setUrl(
				HttpUrl::create()->parse('http://nonexistentdomain.xyz')
			)->
			setMethod(HttpMethod::get());

		$this->expectException(NetworkException::class);
		$this->expectExceptionMessageMatches('/curl\s+error/');
		
		$response = CurlHttpClient::create()->
				setTimeout(3)->
				send($request);
	}
}
?>