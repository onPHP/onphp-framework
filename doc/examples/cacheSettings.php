<?php
// $Id$

require dirname(__FILE__) . '/../../global.inc.php.tpl';

// set up default cache peer

Cache::setPeer(
    new SocketMemcached()
);

// or even several aggregated peers

Cache::setPeer(
    (new AggregateCache())
        ->addPeer(
            'memcached daemon at localhost',
            new SocketMemcached()
        )
        ->addPeer(
            'local low-priority file system',
            new RubberFileSystem('/tmp/onphp-cache'),
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