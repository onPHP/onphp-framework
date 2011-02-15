<?php
	/* $Id$ */
	
	final class CacheTest extends TestCase
	{
		const QUERIES = 100;

		public function testAggregateCache()
		{
			return $this->doTestMemcached(
				AggregateCache::create()->
					addPeer('low', Memcached::create(), AggregateCache::LEVEL_LOW)->
					addPeer('normal1', Memcached::create())->
					addPeer('normal2', Memcached::create())->
					addPeer('normal3', Memcached::create())->
					addPeer('high', Memcached::create(), AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
		}

		public function testSimpleAggregateCache()
		{
			return $this->doTestMemcached(
				SimpleAggregateCache::create()->
					addPeer('low', Memcached::create(), AggregateCache::LEVEL_LOW)->
					addPeer('normal1', Memcached::create())->
					addPeer('normal2', Memcached::create())->
					addPeer('normal3', Memcached::create())->
					addPeer('high', Memcached::create(), AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
		}

		public function testCyclicAggregateCache()
		{
			$this->doTestMemcached(
				CyclicAggregateCache::create()->
					setSummaryWeight(42)->
					addPeer('first', Memcached::create(), 25)->
					addPeer('second', PeclMemcached::create(), 1)->
					addPeer('third', PeclMemcached::create(), 13)
			);
		}
		
		public function testIntegerChanges()
		{
			Cache::me()->set('test_integer', 1);

			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertEquals(
					$i + 2,
					Cache::me()->increment('test_integer', 1)
				);

				$this->assertEquals($i + 2, Cache::me()->get('test_integer'));
			}

			$this->assertEquals(
				self::QUERIES + 1,
				Cache::me()->get('test_integer')
			);

			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertEquals(
					self::QUERIES - $i,
					Cache::me()->decrement('test_integer', 1)
				);

				$this->assertEquals(
					self::QUERIES - $i,
					Cache::me()->get('test_integer')
				);
			}

			$this->assertEquals(Cache::me()->get('test_integer'), 1);
		}
		
		private function doTestMemcached(SelectivePeer $cache)
		{
			Cache::setPeer($cache);
			
			
			if (!Cache::me()->isAlive()) {
				return $this->markTestSkipped('memcached not available');
			}
			
			for ($i = 0; $i < self::QUERIES; ++$i) {
				$this->assertTrue(Cache::me()->mark('one')->set($i, $i));
				$this->assertTrue(Cache::me()->mark('two')->set($i, $i));
			}
		
			$oneHit = 0;
			$twoHit = 0;
		
			for ($i = 0; $i < self::QUERIES; ++$i) {
				if (Cache::me()->mark('one')->get($i) == $i)
					++$oneHit;
				if (Cache::me()->mark('two')->get($i) == $i)
					++$twoHit;
			}
			
			$this->assertEquals($oneHit, $twoHit);
			$this->assertEquals($twoHit, self::QUERIES);
		}
	}
?>