#!/usr/bin/php
<?php
/***************************************************************************
 *   Copyright (C) by Evgeny M. Stepanov                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/* Make sql schema
 *
 * Set path to project config
 * exemple:
 *	$ ./makeSqlSchema.php conf/config.inc.php
 *
 */

if (!isset($argv[1])) {
	exit("error: Set path to project config\n");
}

try {
	include $argv[1];
	include PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'schema.php';

	echo PHP_EOL . $schema->toDialectString(DBPool::me()->getLink()->getDialect());
} catch (Exception $e) {
	file_put_contents(STDERR, $e);
}