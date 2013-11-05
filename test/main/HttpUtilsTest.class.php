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

	namespace Onphp\Test;

	final class HttpUtilsTest extends TestCase
	{
		public function testCurlGet()
		{
			$request = \Onphp\HttpRequest::create()->
				setUrl(
					\Onphp\HttpUrl::create()->parse('https://github.com/')
				)->
				setHeaderVar('User-Agent', 'onphp-test')->
				setMethod(\Onphp\HttpMethod::get());
			
			try {
				$response = \Onphp\CurlHttpClient::create()->
					setTimeout(3)->
					send($request);
			} catch (\Onphp\NetworkException $e) {
				return $this->markTestSkipped('no network available');
			}
			
			$this->assertEquals(
				$response->getStatus()->getId(),
				\Onphp\HttpStatus::CODE_200
			);
			
			$this->assertContains(
				'github',
				$response->getBody()
			);
			
			try {
				$badResponse = \Onphp\CurlHttpClient::create()->
					setTimeout(3)->
					setMaxFileSize(100)-> // github page is bigger than 100 bytes
					send($request);
				$this->fail();
			} catch (\Onphp\NetworkException $e) {
				/* pass */
			}
		}
		
		public function testCurlException()
		{
			$request = \Onphp\HttpRequest::create()->
				setUrl(
					\Onphp\HttpUrl::create()->parse('http://nonexistentdomain.xyz')
				)->
				setMethod(\Onphp\HttpMethod::get());
			
			try {
				$response = \Onphp\CurlHttpClient::create()->
					setTimeout(3)->
					send($request);
				
				$this->fail();
			} catch (\Onphp\NetworkException $e) {
				$this->assertContains('curl error', $e->getMessage());
			}
		}
	}
?>
