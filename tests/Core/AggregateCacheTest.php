<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Cache\AggregateCache;
use OnPHP\Core\Cache\Cache;
use OnPHP\Core\Cache\CyclicAggregateCache;
use OnPHP\Core\Cache\PeclMemcached;
use OnPHP\Core\Cache\SelectivePeer;
use OnPHP\Core\Cache\SimpleAggregateCache;
use OnPHP\Core\Cache\SocketMemcached;
use OnPHP\Tests\TestEnvironment\TestCase;

final class AggregateCacheTest extends TestCase
{
	const QUERIES = 100;

	public function testAggregateCache()
	{
		return $this->doTestMemcached(
			AggregateCache::create()->
				addPeer('low', SocketMemcached::create(), AggregateCache::LEVEL_LOW)->
				addPeer('normal1', SocketMemcached::create())->
				addPeer('normal2', SocketMemcached::create())->
				addPeer('normal3', SocketMemcached::create())->
				addPeer('high', SocketMemcached::create(), AggregateCache::LEVEL_HIGH)->
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
			SimpleAggregateCache::create()->
				addPeer('low', SocketMemcached::create(), AggregateCache::LEVEL_LOW)->
				addPeer('normal1', SocketMemcached::create())->
				addPeer('normal2', SocketMemcached::create())->
				addPeer('normal3', SocketMemcached::create())->
				addPeer('high', SocketMemcached::create(), AggregateCache::LEVEL_HIGH)->
				setClassLevel('one', 0xb000)
		);
	}

	public function testCyclicAggregateCache()
	{
		$this->doTestMemcached(
			CyclicAggregateCache::create()->
				setSummaryWeight(42)->
				addPeer('first', SocketMemcached::create(), 25)->
				addPeer('second', PeclMemcached::create(), 1)->
				addPeer('third', PeclMemcached::create(), 13)
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