<?php
namespace Onphp\NsConverter;

use \Exception;
use \Onphp\NsConverter\Flow\JoinCommand;

require dirname(dirname(__FILE__)).'/conf/config.auto.inc.php';

try {
	$command = new JoinCommand();
	$command->run();
} catch	(Exception $e) {
	var_dump(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
}