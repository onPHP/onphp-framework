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
			
			$value = 'a';
			
			$cache->set('a', $value, Cache::EXPIRES_MEDIUM);
			$cache->set('b', $value, Cache::EXPIRES_MEDIUM);
			
			$this->assertEquals($cache->get('a'), 'a');
			$this->assertEquals($cache->get('b'), 'a');
			
			$list = $cache->getList(array('a', 'b'));
			
			$this->assertEquals(count($list), 2);
			
			foreach ($list as $item)
				$this->assertEquals($item, 'a');
			
			$list = $cache->getList(array('a'));
			
			$this->assertEquals(count($list), 1);
			
			foreach ($list as $item)
				$this->assertEquals($item, 'a');
				
			$list = $cache->getList(array('a', 'b', 'c'));
			
			$this->assertEquals(count($list), 2);
			
			foreach ($list as $item)
				$this->assertEquals($item, 'a');
			
			$list = $cache->getList(array('c'));
			
			$this->assertEquals(count($list), 0);
			
			$cache->clean();
		}
	}
?>