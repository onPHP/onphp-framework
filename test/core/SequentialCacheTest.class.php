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
		public function testMultiCacheAliveLast()
		{
			$alife_peer = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$alife_peer->set('some_key', 'some_value');

			$slave1 = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache

			$slave2 = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache

			$cache = new SequentialCache($alife_peer, array($slave1, $slave2, $alife_peer));

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheAliveFirst()
		{
			$alife_peer = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$alife_peer->set('some_key', 'some_value');
			
			$slave1 = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache

			$slave2 = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache

			$cache = new SequentialCache($alife_peer, array($slave1, $slave1, $slave2));

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheAliveOnly()
		{
			$alife_peer = new PeclMemcached("127.0.0.1", "11211", 0.01);		//some existing memcached
			$alife_peer->set('some_key', 'some_value');
			
			$cache = new SequentialCache($alife_peer);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		/**
		 * @expectedException RuntimeException 
		 */
		public function testMultiCacheNoAlive()
		{
			$slave1 = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache
			$slave2 = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache
			
			$cache = new SequentialCache($slave1, array($slave2));

			$result = $cache->get("some_key");	//will be throw RuntimeException
		}
	}
