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

	class RawResponseTest extends TestCase
	{
		public function testRawResponse()
		{
			$response =
				RawResponse::create()->
					setContent('Goodbye, world!');
			$response->getHeaderCollection()->set('Content-Type', 'text/plain');

			$this->assertEquals('text/plain', $response->getHeader('cOnTeNt-tYpE'));
			$this->assertEquals('Goodbye, world!', $response->getBody());
		}
	}

?>
