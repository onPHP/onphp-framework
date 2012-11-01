<?php

	namespace Onphp\Test;

	final class BaseCachesTest extends TestCase
	{
		public static function cacheProvider()
		{
			return array(
//				array(SocketMemcached::create()),
//				array(SharedMemory::create()),
//				array(PeclMemcached::create()),
				array(\Onphp\RuntimeMemory::create()),
//				array(RubberFileSystem::create())
			);
		}

		/**
		 * @dataProvider cacheProvider
		**/
		public function testClean(\Onphp\CachePeer $cache)
		{
			$this->assertInstanceOf('\Onphp\CachePeer', $cache->clean());
		}

		/**
		 * @dataProvider cacheProvider
		**/
		public function testClients(\Onphp\CachePeer $cache)
		{
			$this->clientTest($cache);
			$this->clientTest($cache->enableCompression());
		}

		/**
		 * @dataProvider cacheProvider
		 * @depends testClients
		**/
		public function testWatermarked(\Onphp\CachePeer $cache)
		{
			$cache = \Onphp\WatermarkedPeer::create($cache);
			$this->clientTest($cache);
			$this->clientTest($cache->enableCompression());
			$this->doTestWrongKeys($cache);
		}

		/**
		 * @dataProvider cacheProvider
		 * @depends testWatermarked
		**/
		public function testWrongKeys(\Onphp\CachePeer $cache)
		{
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('cache not available');
			}
			
			$this->doTestWrongKeys($cache);
		}

		/**
		 * @depends testWrongKeys
		**/
		public function testWithTimeout()
		{
			$cache =
				\Onphp\SocketMemcached::create('localhost')->
				setTimeout(200);

			$cache->add('a', 'b');

			$this->assertEquals($cache->get('a'), 'b');

			$cache->clean();
		}
		
		protected function clientTest(\Onphp\CachePeer $cache)
		{
			if (!$cache->isAlive()) {
				return $this->markTestSkipped('cache not available');
			}

			$this->clientTestSingleGet($cache);
			$this->clientTestMultiGet($cache);
			$this->doExpires($cache);
		}
		
		protected function clientTestSingleGet(\Onphp\CachePeer $cache)
		{
			$cache->clean();
			
			$value = 'a';
			
			$this->assertTrue($cache->set('a', $value, \Onphp\Cache::EXPIRES_MEDIUM));
			$this->assertEquals($cache->get('a'), $value);

			$this->assertTrue($cache->append('a', $value));
			$this->assertEquals($cache->get('a'), $value.$value);

			$this->assertTrue($cache->replace('a', $value));
			$this->assertEquals($cache->get('a'), $value);

			$value = array(1,'s', 1234.18, array(1,'w'));
			$this->assertSame($value, $value);
			$cache->set('a', $value, \Onphp\Cache::EXPIRES_MEDIUM);
			$this->assertSame($cache->get('a'), $value);

			$value = 1;
			$cache->set('a', $value, \Onphp\Cache::EXPIRES_MEDIUM);
			$this->assertEquals($cache->increment('a', 1), $value+1);
			$this->assertEquals($cache->get('a'), $value+1);
			$this->assertEquals($cache->increment('a', 2), $value+3);
			$this->assertEquals($cache->get('a'), $value+3);
			$this->assertEquals($cache->decrement('a', 2), $value+1);
			$this->assertEquals($cache->get('a'), $value+1);

			$value = '25';
			$cache->set('a', $value, \Onphp\Cache::EXPIRES_MEDIUM);
			$cache->append('a', $value);
			$this->assertEquals($cache->get('a'), $value.$value);

			$this->assertTrue($cache->delete('a'));

			$cache->clean();
		}
		
		protected function clientTestMultiGet(\Onphp\CachePeer $cache)
		{
			$cache->clean();
			
			$cache->set('a', 'a', \Onphp\Cache::EXPIRES_MEDIUM);
			$cache->set('b', 2, \Onphp\Cache::EXPIRES_MEDIUM);
			$cache->set('c', 42.28, \Onphp\Cache::EXPIRES_MEDIUM);
			
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

		private function doExpires(\Onphp\CachePeer $cache)
		{
			if ($cache instanceof \Onphp\RuntimeMemory) {
				return $this->markTestSkipped('RuntimeMemory cache expire not implemented');
			}

			$cache->clean();

			$value = 'a';

			// do not set if exist and not expired (RubberFileSystem logic)
			$cache->set('a', $value, \Onphp\Cache::EXPIRES_MAXIMUM);
			$this->assertTrue($cache->set('a', '!!!', 1));
			$this->assertEquals($cache->get('a'), $value);
			$this->assertTrue($cache->replace('a', '!!!', \Onphp\Cache::EXPIRES_MINIMUM));
			$this->assertEquals($cache->get('a'), '!!!');

			$cache->replace('a', $value, 1);
			sleep(2);
			$this->assertFalse($cache->get('a'));

			$cache->clean();
		}
		
		private function doTestWrongKeys(\Onphp\CachePeer $cache)
		{
			$cache->clean();

			$value = 'a';
			// unexist key
			$this->assertNull($cache->get('b'));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->replace('b', $value));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->append('b', $value));
			$this->assertTrue($cache->isAlive());
			$this->assertNull($cache->increment('b', $value));
			$this->assertTrue($cache->isAlive());
			$this->assertNull($cache->decrement('b', $value));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->delete('b'));
			$this->assertTrue($cache->isAlive());

			// wrong key
			$this->assertNull($cache->get(null));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->replace(null, $value));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->append(null, $value));
			$this->assertTrue($cache->isAlive());
			$this->assertNull($cache->increment(null, $value));
			$this->assertTrue($cache->isAlive());
			$this->assertNull($cache->decrement(null, $value));
			$this->assertTrue($cache->isAlive());
			$this->assertFalse($cache->delete(null));

			$this->assertTrue($cache->isAlive());
			$cache->clean();
		}
	}
?>