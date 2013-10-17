<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	class RedirectResponseTest extends TestCase
	{
		public function testRedirectResponse()
		{
			$response =
				RedirectResponse::create()->
					setUrl('http://example.com/')->
					setStatus(new HttpStatus(HttpStatus::CODE_301))
				;

			$this->assertEquals('http://example.com/', $response->getHeader('Location'));
		}

		/**
		 * @expectedException WrongArgumentException
		**/
		public function testInvalidStatus()
		{
			$response =
				RedirectResponse::create()->
					setUrl('http://example.com/')->
					setStatus(new HttpStatus(HttpStatus::CODE_404))
			;
		}
	}
?>
