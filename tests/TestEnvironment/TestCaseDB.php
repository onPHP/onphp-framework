<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\DB\DB;

abstract class TestCaseDB extends TestCase
{
	private $dBCreator = null;

	public function setUp() :void
	{
		parent::setUp();

		$this->dBCreator = DBTestCreator::create()->
			setSchemaPath(ONPHP_META_AUTO_DIR.'schema.php')->
			setTestPool(DBTestPool::me());
	}

	public function tearDown() :void
	{
		parent::tearDown();
	}

	/**
	 * @return DBTestCreator
	 */
	protected function getDBCreator() {
		return $this->dBCreator;
	}

	/**
	 * @param string $type - class (MySQL:class, PgSQL::class ... etc)
	 * @return DB
	 */
	protected function getDbByType($type) {
		foreach (DBTestPool::me()->getPool() as $db) {
			if (get_class($db) == $type)
				return $db;
		}

		$this->fail('couldn\'t get db type "'.$type.'"');
	}
}
?>