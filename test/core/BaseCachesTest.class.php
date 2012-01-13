<?php

	final class BaseCachesTest extends TestCase
	{
		public static function cacheProvider()
		{
			return array(
				array(Memcached::create()),
//				array(SharedMemory::create()),
				array(PeclMemcached::create()),
				array(RuntimeMemory::create()),
				array(RubberFileSystem::create())
			);
		}

		/**
		  * @dataProvider cacheProvider
		  */
		public function testClients(CachePeer $cache)
		{
			$this->clientTest($cache);
		}

		/**
		  * @dataProvider cacheProvider
		  */
		public function testWatermarked(CachePeer $cache)
		{
			$cache = WatermarkedPeer::create($cache);
			$this->clientTest($cache);
			$this->doTestWrongKeys($cache);
		}

		/**
		  * @dataProvider cacheProvider
		  */
		public function testWrongKeys(CachePeer $cache)
		{
			$this->doTestWrongKeys($cache);
		}

		public function testWithTimeout()
		{
			$cache =
				Memcached::create('localhost')->
				setTimeout(200);

			$cache->add('a', 'b');

			$this->assertEquals($cache->get('a'), 'b');

			$cache->clean();
		}
		
		protected function clientTest(CachePeer $cache)
		{
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('cache not available');
			}

			$this->clientTestSingleGet($cache);
			$this->clientTestMultiGet($cache);
		}
		
		protected function clientTestSingleGet(CachePeer $cache)
		{
			$cache->clean();
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->get('a'), $value);

			$cache->append('a', $value);
			$this->assertEquals($cache->get('a'), $value.$value);

			$value = array(1,'s', 1234.18, array(1,'w'));
			$this->assertSame($value, $value);
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$this->assertSame($cache->get('a'), $value);

			$value = 1;
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$cache->increment('a', 1);
			$this->assertEquals($cache->get('a'), $value+1);
			$cache->increment('a', 2);
			$this->assertEquals($cache->get('a'), $value+3);
			$cache->decrement('a', 2);
			$this->assertEquals($cache->get('a'), $value+1);

			$cache->clean();
		}
		
		protected function clientTestMultiGet(CachePeer $cache)
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
		
		private function doTestWrongKeys(CachePeer $cache)
		{
			$cache->get(null);
			
			$this->assertTrue($cache->isAlive());
		}
	}
?>