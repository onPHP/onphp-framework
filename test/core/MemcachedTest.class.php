<?php
	/* $Id$ */
	
	final class MemcachedTest extends TestCase
	{
		public function testClients()
		{
			if (
				substr(
					`echo "stats\r\nquit\r\n" | nc localhost 11211`,
					0,
					4
				)
				!= 'STAT'
			)
				return $this->markTestSkipped('memcached not available');
			
			$this->clientTest(new PeclMemcache());
			$this->clientTest(new PeclMemcached());
			
			$this->clientTest(new SocketMemcached());
		}

		public function testWithTimeout()
		{
			if (
				substr(
					`echo "stats\nquit\n" | nc localhost 11211`,
					0,
					4
				)
				!= 'STAT'
			)
				return $this->markTestSkipped('memcached not available');

			$cache =
				SocketMemcached::create('localhost', 11211)->
				setTimeout(200);

			$cache->add('a', 'b');

			$this->assertEquals($cache->get('a'), 'b');

			$cache->clean();
		}
		
		protected function clientTest($class)
		{
			$this->clientTestSingleGet($class);
			$this->clientTestMultiGet($class);
		}
		
		protected function clientTestSingleGet($cache)
		{
			$cache->clean();
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			
			$cache->clean();
		}
		
		protected function clientTestMultiGet($cache)
		{
			$cache->clean();
			
			$cache->set('a', 'a', Cache::EXPIRES_MEDIUM);
			$cache->set('b', 2, Cache::EXPIRES_MEDIUM);
			$cache->set('c', 42.28, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			$this->assertEquals($cache->get('b'), 2);
			$this->assertEquals($cache->get('c'), 42.28);
			
			$list = $cache->getList(array('a', 'b', 'c'));
			
			$this->assertEquals(count($list), 3);
			
			$this->assertEquals($list['a'], 'a');
			$this->assertEquals($list['b'], 2);
			$this->assertEquals($list['c'], 42.28);
			
			$list = $cache->getList(array('a'));
			
			$this->assertEquals(count($list), 1);
			
			$this->assertEquals($list['a'], 'a');
				
			$list = $cache->getList(array('a', 'b', 'c', 'd'));
			
			$this->assertEquals(count($list), 3);
			
			$this->assertEquals($list['a'], 'a');
			$this->assertEquals($list['b'], 2);
			$this->assertEquals($list['c'], 42.28);
			
			$list = $cache->getList(array('d'));
			
			$this->assertEquals(count($list), 0);
			
			$cache->clean();
		}
	}
?>