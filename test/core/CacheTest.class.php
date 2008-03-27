<?php
	/* $Id$ */
	
	final class CacheTest extends TestCase
	{
		const QUERIES = 100;
		
		public function testMemcached()
		{
			Cache::setPeer(
				AggregateCache::create()->
					addPeer('low', Memcached::create(), AggregateCache::LEVEL_LOW)->
					addPeer('normal1', Memcached::create())->
					addPeer('normal2', Memcached::create())->
					addPeer('normal3', Memcached::create())->
					addPeer('high', Memcached::create(), AggregateCache::LEVEL_HIGH)->
					setClassLevel('one', 0xb000)
			);
			
			if (!Cache::me()->isAlive()) {
				return $this->skip();
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