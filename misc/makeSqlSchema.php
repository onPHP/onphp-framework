#!/usr/bin/php
<?php

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
}catch (Exception $e) {
	file_put_contents('php://outerr', "$e");
}