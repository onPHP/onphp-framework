<?php

	namespace Onphp\Test;

	final class AggregateCacheTest extends TestCase
	{
		const QUERIES = 100;

		public function testAggregateCache()
		{
			return $this->doTestMemcached(
				\Onphp\AggregateCache::create()->
					addPeer('low', \Onphp\SocketMemcached::create(), \Onphp\AggregateCache::LEVEL_LOW)->
					addPeer('normal1', \Onphp\SocketMemcached::create())->
					addPeer('normal2', \Onphp\SocketMemcached::create())->
					addPeer('normal3', \Onphp\SocketMemcached::create())->
					addPeer('high', \Onphp\SocketMemcached::create(), \Onphp\AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
		}
/*
 * temporary disabled. fix base cache peers first
		public function testCompositeAggregateCache()
		{
			return $this->doTestMemcached(
				AggregateCache::create()->
					addPeer('low', Memcached::create(), AggregateCache::LEVEL_LOW)->
					addPeer('normal', RuntimeMemory::create())->
					addPeer('high', RubberFileSystem::create(), AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
		}
*/
		public function testSimpleAggregateCache()
		{
			return $this->doTestMemcached(
				\Onphp\SimpleAggregateCache::create()->
					addPeer('low', \Onphp\SocketMemcached::create(), \Onphp\AggregateCache::LEVEL_LOW)->
					addPeer('normal1', \Onphp\SocketMemcached::create())->
					addPeer('normal2', \Onphp\SocketMemcached::create())->
					addPeer('normal3', \Onphp\SocketMemcached::create())->
					addPeer('high', \Onphp\SocketMemcached::create(), \Onphp\AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
		}

		public function testCyclicAggregateCache()
		{
			$this->doTestMemcached(
				\Onphp\CyclicAggregateCache::create()->
					setSummaryWeight(42)->
					addPeer('first', \Onphp\SocketMemcached::create(), 25)->
					addPeer('second', \Onphp\PeclMemcached::create(), 1)->
					addPeer('third', \Onphp\PeclMemcached::create(), 13)
			);
		}
/*
 * temporary disabled. fix base cache peers first
		public function testCompositeCyclicAggregateCache()
		{
			$this->doTestMemcached(
				CyclicAggregateCache::create()->
					setSummaryWeight(42)->
					addPeer('first', Memcached::create(), 25)->
					addPeer('second', RuntimeMemory::create(), 1)->
					addPeer('third', RubberFileSystem::create(), 13)
			);
		}
*/
		public function testIntegerChanges()
		{
			\Onphp\Cache::me()->set('test_integer', 1);

			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertEquals(
					$i + 2,
					\Onphp\Cache::me()->increment('test_integer', 1)
				);

				$this->assertEquals($i + 2, \Onphp\Cache::me()->get('test_integer'));
			}

			$this->assertEquals(
				self::QUERIES + 1,
				\Onphp\Cache::me()->get('test_integer')
			);

			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertEquals(
					self::QUERIES - $i,
					\Onphp\Cache::me()->decrement('test_integer', 1)
				);

				$this->assertEquals(
					self::QUERIES - $i,
					\Onphp\Cache::me()->get('test_integer')
				);
			}

			$this->assertEquals(\Onphp\Cache::me()->get('test_integer'), 1);
		}
		
		private function doTestMemcached(\Onphp\SelectivePeer $cache)
		{
			\Onphp\Cache::setPeer($cache);

			if (!\Onphp\Cache::me()->isAlive()) {
				return $this->markTestSkipped('memcached not available');
			}
			
			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertTrue(\Onphp\Cache::me()->mark('one')->set($i, $i));
				$this->assertTrue(\Onphp\Cache::me()->mark('two')->set($i, $i));
			}
		
			$oneHit = 0;
			$twoHit = 0;
		
			for ($i = 0; $i < self::QUERIES; ++$i) {
				if (\Onphp\Cache::me()->mark('one')->get($i) == $i)
					++$oneHit;
				if (\Onphp\Cache::me()->mark('two')->get($i) == $i)
					++$twoHit;
			}
			
			$this->assertEquals($oneHit, $twoHit);
			$this->assertEquals($twoHit, self::QUERIES);
		}
	}
?>