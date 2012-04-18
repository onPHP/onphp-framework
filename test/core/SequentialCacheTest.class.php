<?php
/****************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko									*
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	final class SequentialCacheTest extends TestCase
	{
		public function testMultiCacheMaterLast() {
			$t = microtime(true);
			$master = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$master->set('some_key', 'some_value');

			$slave1 = new LazyObject(function(){
				$obj = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache
				return $obj;
			});

			$slave2 = new LazyObject(function(){
				$obj = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache
				return $obj;
			});

			$cache = new SequentialCache($slave1, $slave1, $slave2, $master);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheMasterFirst() {
			$master = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$master->set('some_key', 'some_value');
			
			$slave1 = new LazyObject(function(){
				$obj = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache
				return $obj;
			});

			$slave2 = new LazyObject(function(){
				$obj = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache
				return $obj;
			});

			$cache = new SequentialCache($master, $slave1, $slave1, $slave2);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheMasterOnly() {
			$master = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$master->set('some_key', 'some_value');
			
			$cache = new SequentialCache($master);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		/**
		 * @expectedException RuntimeException 
		 */
		public function testMultiCacheNoMaster() {
			$slave1 = new LazyObject(function(){
				$obj = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache
				return $obj;
			});

			$slave2 = new LazyObject(function(){
				$obj = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache
				return $obj;
			});
			
			$cache = new SequentialCache($slave1, $slave2);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
	}
