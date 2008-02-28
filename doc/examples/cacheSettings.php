<?php
	// $Id$

	require dirname(__FILE__).'/../../global.inc.php.tpl';

	// set up default cache peer

	Cache::setPeer(
		Memcached::create()
	);

	// or even several aggregated peers
	
	Cache::setPeer(
		AggregateCache::create()->
		addPeer(
			'memcached daemon at localhost',
			Memcached::create()
		)->
		addPeer(
			'local low-priority file system',
			RubberFileSystem::create('/tmp/onphp-cache'),
			AggregateCache::LEVEL_VERYLOW
		)
	);

	// let's test out cache system

	$ts = new Timestamp(time());

	$key = 'timestamp_object';

	if (Cache::me()->set($key, $ts, 2)) {
		echo "object is in cache now\n";

		if ($cached = Cache::me()->get($key)) {
			echo "got from cache:\n";
			print_r($cached);
		}

	} else {
		echo "failed to store object in cache\n";
	}
?>