#!/usr/bin/php
<?php

function helpMessage()
{
	echo "~~~ [Make sql schema] ~~~\nHelp: \nSet path to project config, exemple: \n\t$ ".$_SERVER['argv'][0]." conf/config.inc.php \n";
}

$pathConfig = null;

if (isset($argv[1])) {
	$pathConfig = $argv[1];
}
else {
	helpMessage();
	exit(1);
}
try {

	include $pathConfig;
	include PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'schema.php';

	$sql = $schema->toDialectString(DBPool::me()->getLink()->getDialect());

	$pathProject =
		defined('PATH_PROJECT')? PATH_PROJECT:
		(defined('PATH_PROJECT_ROOT')? PATH_PROJECT_ROOT:
		(defined('POJECT_ROOT')? POJECT_ROOT: getcwd().DIRECTORY_SEPARATOR));

	$pathFile = $pathProject.'db'.DIRECTORY_SEPARATOR.'schema.'.(get_class(DBPool::me()->getLink())).'.sql';
	$file = fopen($pathFile ,'w+');
	fputs($file, $sql);
	fclose($file);

	echo "\n Done. Save to $pathFile \n";

}catch (Exception $e) {
	throw $e;
}