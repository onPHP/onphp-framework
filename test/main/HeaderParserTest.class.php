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

	class HeaderParserTest extends UnitTestCase
	{
		public function testSimple()
		{
			$raw = <<<EOT
HTTP/1.1 200 OK
Date: Tue, 31 Jul 2007 14:30:10 GMT
Server: Apache/1.3.27 (Unix) PHP/3.0.18 rus/PL30.16
X-Powered-By: PHP/3.0.18
Location: /eng/index.html
Connection: close
Transfer-Encoding: chunked
Content-Type: text/html; charset=koi8-r
Content-Language: ru
Vary: accept-charset, user-agent
EOT;
			$parser = HeaderParser::create()->parse($raw);
			$this->assertEqual(9, count($parser->getHeaders()));
			$this->assertEqual('close', $parser->getHeader('connection'));
		}
		
		public function testMultiline()
		{
			$raw = <<<EOT
HTTP/1.1 200 OK
Date: Tue, Jul 31 18:23:39 MSD 2007
Server: Nginx
Content-Length: 123
Keep-Alive: timeout=20, 
	max=200
Connection: Keep-Alive
Content-Type: text/html;
  charset=utf-8
EOT;
			$parser = HeaderParser::create()->parse($raw);
			$this->assertEqual(6, count($parser->getHeaders()));
			$this->assertEqual($parser->getHeader('keep-alive'), 'timeout=20, max=200');
			$this->assertEqual($parser->getHeader('content-length'), '123');
		}
	}
?>