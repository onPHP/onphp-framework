<?php
	namespace Onphp\Test;

	final class SocketTest extends TestCase
	{
		public function testShutdown()
		{
			\Onphp\Socket::create()->
				setHost('localhost')->
				setPort(80)->
				close();
			
			$timedOutSocket =
				\Onphp\Socket::create()->
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