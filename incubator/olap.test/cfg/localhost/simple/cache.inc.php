<?php
	/* $Id$ */
	
	Cache::setPeer(
		WatermarkedPeer::create(
			Memcached::create(),
			Application::me()->getName()
		)
	);
	
	Cache::setDefaultWorker('VoodooDaoWorker');
	VoodooDaoWorker::setDefaultHandler('MessageSegmentHandler');
?>