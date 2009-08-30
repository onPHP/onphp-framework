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

	final class HttpUtilsTest extends UnitTestCase
	{
		public function testCurlGet()
		{
			$request = HttpRequest::create()->
				setUrl(
					HttpUrl::create()->parse('http://onphp.org/')
				)->
				setMethod(HttpMethod::get());
			
			try {
				$response = CurlHttpClient::create()->
					setTimeout(3)->
					send($request);
			} catch (NetworkException $e) {
				// ok, we're networkless
				return $this->skip();
			}
			
			$this->assertEqual(
				$response->getStatus()->getId(),
				HttpStatus::CODE_200
			);
			
			$this->assertPattern(
				'/quite official site/',
				$response->getBody()
			);
			
			try {
				$badResponse = CurlHttpClient::create()->
					setTimeout(3)->
					setMaxFileSize(100)-> // onPHP page is bigger than 100 bytes
					send($request);
				$this->fail();
			} catch (NetworkException $e) {
				$this->pass();
			}
		}
		
		public function testCurlException()
		{
			$request = HttpRequest::create()->
				setUrl(
					HttpUrl::create()->parse('http://nonexistentdomain.xxx')
				)->
				setMethod(HttpMethod::get());
			
			try {
				$response = CurlHttpClient::create()->
					setTimeout(3)->
					send($request);
				
				$this->fail();
			} catch (NetworkException $e) {
				$this->assertPattern('/curl error/', $e->getMessage());
			}
		}
	}
?>