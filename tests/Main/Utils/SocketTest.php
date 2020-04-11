<?php

namespace OnPHP\Tests\Main\Utils;

use OnPHP\Main\Util\IO\Socket;
use OnPHP\Tests\TestEnvironment\TestCase;

final class SocketTest extends TestCase
{
	public function testShutdown()
	{
		Socket::create()->
			setHost('localhost')->
			setPort(80)->
			close();

		$timedOutSocket =
			Socket::create()->
				setHost('google.com')->
				setPort(80)->
				setTimeout(1)->
				connect();

		$timedOutSocket->write("GET / HTTP/1.1\r\nHost: google.com\r\n\r\n");
		$timedOutSocket->read(256);

		$timedOutSocket->close();

		$this->assertEquals(42, 42);
	}
}
?>