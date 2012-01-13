<?php
	/* $Id$ */
	
	final class MemcachedTest extends TestCase
	{
		public function testClients()
		{
			$this->clientTest(new PeclMemcached('localhost'));
			$this->clientTest(new Memcached('localhost'));
			$this->clientTest(Redis::create());
		}
		
		public function testWrongKeys()
		{
			$this->doTestWrongKeys(new Memcached('localhost'));
			$this->doTestWrongKeys(new PeclMemcached('localhost'));
			$this->doTestWrongKeys(Redis::create());
		}

		public function testWithTimeout()
		{
			$this->withTimeout(Memcached::create('localhost'));
//			$this->withTimeout(new Redis());
		}

		protected function withTimeout(CachePeer $cache)
		{
			$cache->setTimeout(200);

			$cache->add('a', 'b');

			$this->assertEquals($cache->get('a'), 'b');

			$cache->clean();
		}

		protected function clientTest(CachePeer $cache)
		{
			$this->clientTestSingleGet($cache);
			$this->clientTestMultiGet($cache);
		}
		
		protected function clientTestSingleGet(CachePeer $cache)
		{
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('cache not available');
			}
			
			$cache->clean();
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('a'), $value);

			$cache->append('a', $value);
			$this->assertEquals($cache->get('a'), $value.$value);

			$value = 'L'.str_repeat('o', 256).'ng string';
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('a'), $value);

			$cache->delete('a');

			$cache->set('a', 1, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('a'), 1);
			$cache->increment('a', 1);
			$this->assertEquals($cache->get('a'), 2);
			$cache->decrement('a', 2);
			$this->assertEquals($cache->get('a'), 0);

			$cache->set('c', 42.28, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('c'), 42.28);

			$cache->replace('c', 42.297, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('c'), 42.297);

			$cache->clean();
		}
		
		protected function clientTestMultiGet(CachePeer $cache)
		{
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
		
		private function doTestWrongKeys(CachePeer $cache)
		{
			$this->assertNull($cache->get(null));

			$this->assertTrue($cache->isAlive());
		}
	}
?>