<?php
namespace Onphp\NsConverter;

require dirname(dirname(__FILE__)).'/conf/config.auto.inc.php';

try {
	$command = new ScanCommand();
	$command->run();
} catch	(\Exception $e) {
	var_dump(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
}