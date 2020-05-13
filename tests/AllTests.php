<?php

namespace OnPHP\Tests;

use OnPHP\Core\Base\Singleton;
use OnPHP\Core\DB\DBPool;
use OnPHP\Meta\Entity\MetaConfiguration;
use OnPHP\Tests\TestEnvironment\DBTestCreator;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestSuite;

date_default_timezone_set('Europe/Moscow');
define('ONPHP_TEST_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

require ONPHP_TEST_PATH.'../global.inc.php.tpl';

define('ENCODING', 'UTF-8');

mb_internal_encoding(ENCODING);
mb_regex_encoding(ENCODING);

define('BASE_PATH', ONPHP_TEST_PATH."");

$testPathes = array(
	ONPHP_TEST_PATH.'Core'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR,
	//ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Autoloader'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Ip'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'Routers'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'Main'.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'AMQP'.DIRECTORY_SEPARATOR,
	ONPHP_TEST_PATH.'DB'.DIRECTORY_SEPARATOR,
);


$config = dirname(__FILE__).'/config.inc.php';

include is_readable($config) ? $config : $config.'.tpl';

final class AllTests
{
	public static $dbs = null;
	public static $paths = null;
	public static $workers = null;

	public static function suite()
	{
		$suite = new TestSuite('onPHP-'.ONPHP_VERSION);

		// meta, DB and DAOs ordered tests portion
		if (self::$dbs) {
			try {
				/**
				 * @todo fail - constructor with argument, but static method 'me' - without
				 */
				Singleton::getInstance(DBTestPool::class, self::$dbs)->connect();
			} catch (\Exception $e) {
				Singleton::dropInstance(DBTestPool::class);
				Singleton::getInstance(DBTestPool::class);
			}

			// build stuff from meta

			$metaDir = ONPHP_TEST_PATH.'Meta'.DIRECTORY_SEPARATOR;
			$path = ONPHP_META_PATH.'build.php';

			$_SERVER['argv'] = array();

			$_SERVER['argv'][0] = $path;

			$_SERVER['argv'][1] = $metaDir.'config.inc.php';

			$_SERVER['argv'][2] = $metaDir.'config.meta.xml';

			$_SERVER['argv'][] = '--force';
			$_SERVER['argv'][] = '--no-schema-check';
			$_SERVER['argv'][] = '--drop-stale-files';

			include $path;
			
			$dBCreator = DBTestCreator::create()->
				setSchemaPath(ONPHP_META_AUTO_DIR.'schema.php')->
				setTestPool(DBTestPool::me());
			
			$out = MetaConfiguration::me()->getOutput();

			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);

				$out->
					info('Using ')->
					info(get_class($db), true)->
					infoLine(' connector.');

				$dBCreator->dropDB(true);

				$dBCreator->createDB()->fillDB();

				MetaConfiguration::me()->checkIntegrity();
				$out->newLine();

				$dBCreator->dropDB();
			}

			DBPool::me()->dropDefault();
		}

		foreach (self::$paths as $testPath) {
			foreach (glob($testPath.'*Test'.EXT_CLASS, GLOB_BRACE) as $file) {
				$suite->addTestFile($file);
			}
		}
		
		return $suite;
	}
}

AllTests::$dbs = $dbs;
AllTests::$paths = $testPathes;
AllTests::$workers = $daoWorkers;
?>