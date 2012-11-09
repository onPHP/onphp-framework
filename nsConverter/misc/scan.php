<?php
namespace Onphp\NsConverter;

use \Exception;
use \Onphp\NsConverter\Flow\ScanCommand;

require dirname(dirname(__FILE__)).'/conf/config.auto.inc.php';

try {
	$command = new ScanCommand();
	$command->run();
} catch	(Exception $e) {
	var_dump(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
}