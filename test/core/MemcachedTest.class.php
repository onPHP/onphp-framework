<?php
	/* $Id$ */
	
	final class MemcachedTest extends TestCase
	{
		public function testSingleGet()
		{
			$cache = Memcached::create('localhost');
			
			$cache->clean();
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			
			$cache->clean();
		}
		
		public function testMultiGet()
		{
			$cache = Memcached::create('localhost');

			$cache->clean();
			
			$cache->set('a', 'a', Cache::EXPIRES_MEDIUM);
			$cache->set('b', 2, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			$this->assertEquals($cache->get('b'), 2);
			
			$list = $cache->getList(array('a', 'b'));
			
			$this->assertEquals(count($list), 2);
			
			$this->assertEquals($list[0], 'a');
			$this->assertEquals($list[1], 2);
			
			$list = $cache->getList(array('a'));
			
			$this->assertEquals(count($list), 1);
			
			$this->assertEquals($list[0], 'a');
				
			$list = $cache->getList(array('a', 'b', 'c'));
			
			$this->assertEquals(count($list), 2);
			
			$this->assertEquals($list[0], 'a');
			$this->assertEquals($list[1], 2);
			
			$list = $cache->getList(array('c'));
			
			$this->assertEquals(count($list), 0);
			
			$cache->clean();
		}
	}
?>