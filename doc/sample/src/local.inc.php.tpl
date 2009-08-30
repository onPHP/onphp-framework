<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov                     *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	// sample local configuration file

	// db settings
	define('DB_BASE', 'dataBaseName');
	define('DB_USER', 'userName');
	define('DB_PASS', 'p*ssW*rd');
	define('DB_HOST', 'hostName');
	define('DB_CLASS', 'PgSQL');

	Cache::setPeer(
		AggregateCache::create()->
			addPeer('localhost', Memcached::create())->
			addPeer('fallback', RuntimeMemory::create())
	);
?>