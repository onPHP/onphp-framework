<?php
	/***************************************************************************
	*   Copyright (C) 2012 by Artem Naumenko	                              *
	*                                                                         *
	*   This program is free software; you can redistribute it and/or modify  *
	*   it under the terms of the GNU Lesser General Public License as        *
	*   published by the Free Software Foundation; either version 3 of the    *
	*   License, or (at your option) any later version.                       *
	*                                                                         *
	***************************************************************************/
	
	final class RedisNoSQLTest extends TestCase
	{
		public function setUp()
		{
			if (!extension_loaded('redis'))
				$this->markTestSkipped('Install phpredis https://github.com/nicolasff/phpredis/');
			
			$redis = new RedisNoSQL('localhost', 6379);

			if (!$redis->isAlive()) {
				$this->markTestSkipped('Can\'t connect to redis server at localhost');
			}
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

			$redis->delete('some_key');
		}
		
		public function testList()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete('list');
			
			$list	= $redis->fetchList('list');
			$this->assertEquals(count($list), 0);
			
			$list->append('preved');
			$list->append('medved');
			
			$this->assertEquals($list->count(), 2);
			
			$redis->delete('list');
		}
		
		public function testListIterator()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete('list');
			
			$list	= $redis->fetchList('list');
			$list->append('preved');
			$list->append('medved');
			
			$string = '';
			
			foreach ($list as $val) {
				$string .= $val.'_';
			}
			
			$this->assertEquals($string, 'preved_medved_');
			
			$redis->delete('list');
		}
		
		public function testListArrayAccess()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete('list');
			
			$list	= $redis->fetchList('list');
			$list->append('preved');
			$list->append('medved');
			
			$this->assertEquals($list[1], 'medved');
			
			$redis->delete('list');
		}
		
		public function testListTrim()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete('list');
			
			$list	= $redis->fetchList('list');
			
			for ($i = 0; $i < 100; $i ++) {
				$list->append(md5($i));
			}
			
			$this->assertEquals($list->count(), 100);
			
			$list->trim(0, 10);
			$this->assertEquals($list->count(), 10);
			$this->assertEquals($list[0], md5(0));
			
			$redis->delete('list');
		}
		
		public function testListClean()
		{
			$redis	= new RedisNoSQL('localhost', 6379);
			$redis->delete('list');
			
			$list	= $redis->fetchList('list');
			
			for ($i = 0; $i < 100; $i ++) {
				$list->append(md5($i));
			}
			
			$this->assertEquals(count($list), 100);
			$list->clear();
			$this->assertEquals(count($list), 0);
			$this->assertEquals($list->get(0), false);
			
			$redis->delete('list');
		}
	}
