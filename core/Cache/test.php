<?php
	include './CachePeer.class.php';
	include './Cache.class.php';
	include './Memcached.class.php';
	include '../../../src/global.inc.php';

	Cache::setPeer(
		AggregateCache::create()->
			addPeer('low', Memcached::create(), AggregateCache::LEVEL_LOW)->
			addPeer('normal1', Memcached::create())->
			addPeer('normal2', Memcached::create())->
			addPeer('normal3', Memcached::create())->
			addPeer('high', Memcached::create(), AggregateCache::LEVEL_HIGH)->
			setClassLevel('one', 0xb000)
	);

	for ($i = 0; $i < 10000; $i++) {
		Cache::me()->mark('one')->set($i, $i);
		Cache::me()->mark('two')->set($i, $i);
	}

	$oneHit = 0;
	$twoHit = 0;

	for ($i = 0; $i < 10000; $i++) {
		if (Cache::me()->mark('one')->get($i) == $i)
			$oneHit++;
		if (Cache::me()->mark('two')->get($i) == $i)
			$twoHit++;
	}

	echo "one hit $oneHit\n";
	echo "two hit $twoHit\n";

	var_export(Cache::me()->getStats());
?>
