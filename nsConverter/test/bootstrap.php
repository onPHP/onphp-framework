<?php
namespace Onphp\NsConverter;

define('TEST_MODE', true);
require dirname(dirname(__FILE__)).'/conf/config.auto.inc.php';
\Onphp\AutoloaderClassPathCache::create()
	->setNamespaceResolver(\Onphp\NamespaceResolverOnPHP::create())
	->addPaths([
		PATH_BASE.'test'.DIRECTORY_SEPARATOR.'utils'.DIRECTORY_SEPARATOR,
	], 'Onphp\NsConverter')
	->register();