<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	// sample local configuration file

	// db settings
	define('DB_BASE', 'dataBaseName');
	define('DB_USER', 'userName');
	define('DB_PASS', 'p*ssW*rd');
	define('DB_HOST', 'hostName');
	define('DB_CLASS', 'PgSQL');

	// memcached settings
	define('MEMCACHED_PORT', 11211);
	define('MEMCACHED_COMPRESSION', true);
	define('MEMCACHED_BUFFER', 1024);

	Memcached::getInstance()->
		addServer('127.0.0.1')/*->dropEverything()*/;
?>