<?php

	final class RedisNoSQLTest extends TestCase
	{
		public function setUp()
		{
			if (!extension_loaded('redis'))
				$this->markTestSkipped('Install phpredis https://github.com/nicolasff/phpredis/');
		}
		
		public function testCachePeer()
		{
			$redis = new RedisNoSQL('localhost', 6379);
			$redis->set('some_key', 'some_value');
			$result = $redis->get('some_key');
			$this->assertEquals($result, 'some_value');
			
			$redis->set('some_key', 'other_value');
			$result = $redis->get('some_key');
			$this->assertEquals($result, 'other_value');
			
			$redis->delete('some_key');
			$result = $redis->get('some_key');
			$this->assertEquals($result, false);
			
			$redis->delete("some_key");		//cleanup
		}
		
		public function testList()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete("list");		//ensure empty
			
			$list	= $redis->getList("list");
			$this->assertEquals($list->count(), 0);
			
			$list->append("preved");
			$list->append("medved");
			
			$this->assertEquals($list->count(), 2);
			
			$redis->delete("list");		//cleanup
		}
		
		public function testListIterator()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete("list");		//ensure empty
			
			$list	= $redis->getList("list");
			$list->append("preved");
			$list->append("medved");
			
			$string = "";
			
			foreach ($list as $val) {
				$string .= $val."_";
			}
			
			$this->assertEquals($string, "preved_medved_");
			
			$redis->delete("list");		//cleanup
		}
		
		public function testListArrayAccess()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete("list");		//ensure empty
			
			$list	= $redis->getList("list");
			$list->append("preved");
			$list->append("medved");
			
			$this->assertEquals($list[1], "medved");
			
			$redis->delete("list");		//cleanup
		}
		
		public function testListTrim()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete("list");		//ensure empty
			
			$list	= $redis->getList("list");
			
			for ($i = 0; $i < 100; $i ++) {
				$list->append(md5($i));
			}
			
			$this->assertEquals($list->count(), 100);
			
			$list->trim(0, 10);
			$this->assertEquals($list->count(), 10);
			$this->assertEquals($list[0], md5(0));
			
			$redis->delete("list");		//cleanup
		}
	}
