<?php
	/* $Id$ */
	
	final class MemcachedTest extends TestCase
	{
		public function testClients()
		{
			$this->clientTest('PeclMemcached');
			$this->clientTest('SocketMemcached');
		}

		public function testWithTimeout()
		{
			$cache =
				SocketMemcached::create('localhost')->
				setTimeout(200);

			$cache->add('a', 'b');

			$this->assertEquals($cache->get('a'), 'b');

			$cache->clean();
		}
		
		protected function clientTest($className)
		{
			$this->clientTestSingleGet($className);
			$this->clientTestMultiGet($className);
		}
		
		protected function clientTestSingleGet($className)
		{
			$cache = new $className('localhost');
			
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('memcached not available');
			}
			
			$cache->clean();
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			
			$cache->clean();
		}
		
		protected function clientTestMultiGet($className)
		{
			$cache = new $className('localhost');
			
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('memcached not available');
			}
			
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