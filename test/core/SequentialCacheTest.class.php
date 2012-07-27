<?php
/****************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko                                   *
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
			$alifePeer = new PeclMemcached("127.0.0.1", "11211"); //some existing memcached
			$alifePeer->set('some_key', 'some_value');
			
			$deadPeer = new SocketMemcached("165.42.42.42", "11211"); //some not existing memcache
			
			$slave1 = new PeclMemcached("35.143.65.241", "11211"); //some not existing memcache

			$slave2 =
				AggregateCache::create()->
				addPeer('dead', new PeclMemcached("165.34.176.221", "11211"))-> //some not existing memcache
				addPeer('dead_too', new PeclMemcached("165.34.176.222", "11211")); //some not existing memcache

			$cache = new SequentialCache($deadPeer, array($slave1, $slave2, $alifePeer));

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheAliveFirst()
		{
			$alifePeer = new SocketMemcached("127.0.0.1", "11211"); //some existing memcached
			$alifePeer->set('some_key', 'some_value');
			
			$slave1 = new PeclMemcached("35.143.65.241", "11211"); //some not existing memcache

			$slave2 = new PeclMemcached("165.34.176.221", "11211"); //some not existing memcache

			$cache = new SequentialCache($alifePeer, array($slave1, $slave1, $slave2));

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		public function testMultiCacheAliveOnly()
		{
			$alifePeer =
				CyclicAggregateCache::create()-> //some existing memcached
				setSummaryWeight(42)->
				addPeer('first', new PeclMemcached("127.0.0.1", "11211"), 0)->
				addPeer('second', new SocketMemcached("127.0.0.1", "11211"), 21);
			
			$alifePeer->set('some_key', 'some_value');
			
			$cache = new SequentialCache($alifePeer);

			$result = $cache->get("some_key");

			$this->assertEquals($result, 'some_value');
		}
		
		/**
		 * @expectedException RuntimeException 
		 */
		public function testMultiCacheNoAlive()
		{
			$dead1 = new PeclMemcached("35.143.65.241", "11211", 0.01);	//some not existing memcache
			$dead2 = new PeclMemcached("165.34.176.221", "11211", 0.01);	//some not existing memcache
			
			$cache = new SequentialCache($dead1, array($dead2));

			$result = $cache->get("some_key");	//will throw RuntimeException
		}
	}
