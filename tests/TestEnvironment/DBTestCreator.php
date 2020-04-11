<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Cache\Cache;
use OnPHP\Core\DB\DB;
use OnPHP\Core\Exception\DatabaseException;
use OnPHP\Core\Exception\ObjectNotFoundException;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBSchema;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Net\HttpUrl;
use OnPHP\Tests\DB\DataDBTest;
use OnPHP\Tests\Meta\Business\Credentials;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\Meta\Business\TestEncapsulant;
use OnPHP\Tests\Meta\Business\TestUser;


class DBTestCreator
{
	/**
	 * @var DBSchema
	 */
	private $schema = null;
	/**
	 * @var DBTestPool
	 */
	private $pool = null;

	/**
	 * @return DBTestCreator
	 */
	public static function create()
	{
		return new self;
	}

	/**
	 * @param string $path
	 * @return DBTestCreator
	 */
	public function setSchemaPath($path)
	{
		require $path;
		Assert::isTrue(isset($schema));
		Assert::isInstance($schema, DBSchema::class);
		$this->schema = $schema;
		return $this;
	}

	/**
	 * @param DBTestPool $testPool
	 * @return DBTestCreator
	 */
	public function setTestPool(DBTestPool $testPool) {
		$this->pool = $testPool;
		return $this;
	}

	/**
	 * @return DBTestCreator
	 */
	public function createDB() {
		/**
		 * @see testRecursionObjects() and meta
		 * for TestParentObject and TestChildObject
		**/
		$this->schema->
			getTableByName('test_parent_object')->
			getColumnByName('root_id')->
			dropReference();

		foreach ($this->pool->getPool() as $name => $db) {
			foreach ($this->schema->getTables() as $name => $table) {
				$db->queryRaw($table->toDialectString($db->getDialect()));
			}
		}

		return $this;
	}

	/**
	 * @param bool $clean
	 * @return DBTestCreator
	 * @throws DatabaseException
	 */
	public function dropDB($clean = false)
	{
		foreach ($this->pool->getPool() as $name => $db) {
			/* @var $db DB */
			foreach ($this->schema->getTableNames() as $name) {
				try {
					$db->queryRaw(
						OSQL::dropTable($name, true)->toDialectString(
							$db->getDialect()
						)
					);
				} catch (DatabaseException $e) {
					if (!$clean)
						throw $e;
				}

				if ($db->hasSequences()) {
					foreach (
						$this->schema->getTableByName($name)->getColumns()
							as $columnName => $column)
					{
						try {
							if ($column->isAutoincrement())
								$db->queryRaw("DROP SEQUENCE {$name}_id;");
						} catch (DatabaseException $e) {
							if (!$clean)
								throw $e;
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @param TestCase $test
	 * @return DBTestCreator
	 */
	public function fillDB(TestCase $test = null)
	{
		$moscow =
			TestCity::create()->
			setName('Moscow');

		$piter =
			TestCity::create()->
			setName('Saint-Peterburg');

		$mysqler =
			TestUser::create()->
			setCity($moscow)->
			setCredentials(
				Credentials::create()->
				setNickname('mysqler')->
				setPassword(sha1('mysqler'))
			)->
			setLastLogin(
				Timestamp::create(time())
			)->
			setRegistered(
				Timestamp::create(time())->modify('-1 day')
			);

		$postgreser = clone $mysqler;
		
		$postgreser->
			setCredentials(
				Credentials::create()->
				setNickName('postgreser')->
				setPassword(sha1('postgreser'))
			)->
			setCity($piter)->
			setUrl(HttpUrl::create()->parse('http://postgresql.org/'));

		$piter = TestCity::dao()->add($piter);
		$moscow = TestCity::dao()->add($moscow);
		
		if ($test) {
			$test->assertEquals($piter->getId(), 1);
			$test->assertEquals($moscow->getId(), 2);
		}

		$postgreser = TestUser::dao()->add($postgreser);
		
		for ($i = 0; $i < 10; $i++) {
			$encapsulant = TestEncapsulant::dao()->add(
				TestEncapsulant::create()->
				setName($i)
			);
			
			$encapsulant->getCities()->
				fetch()->
				setList(
					array($piter, $moscow)
				)->
				save();
		}
		
		$mysqler = TestUser::dao()->add($mysqler);
		
		if ($test) {
			$test->assertEquals($postgreser->getId(), 1);
			$test->assertEquals($mysqler->getId(), 2);
		}

		if ($test) {
			// put them in cache now
			TestUser::dao()->dropIdentityMap();

			TestUser::dao()->getById(1);
			TestUser::dao()->getById(2);

			if ($test instanceof DataDBTest) {
				$test->getListByIdsTest();
			}

			Cache::me()->clean();

			$test->assertTrue(
				($postgreser == TestUser::dao()->getById(1))
			);

			$test->assertTrue(
				($mysqler == TestUser::dao()->getById(2))
			);
		}

		$firstClone = clone $postgreser;
		$secondClone = clone $mysqler;

		$firstCount = TestUser::dao()->dropById($postgreser->getId());
		$secondCount = TestUser::dao()->dropByIds(array($mysqler->getId()));

		if ($test) {
			$test->assertEquals($firstCount, 1);
			$test->assertEquals($secondCount, 1);

			try {
				TestUser::dao()->getById(1);
				$test->fail();
			} catch (ObjectNotFoundException $e) {
				/* pass */
			}

			$result =
				Criteria::create(TestUser::dao())->
				add(Expression::eq(1, 2))->
				getResult();

			$test->assertEquals($result->getCount(), 0);
			$test->assertEquals($result->getList(), array());
		}

		TestUser::dao()->import($firstClone);
		TestUser::dao()->import($secondClone);

		if ($test && $test instanceof DataDBTest) {
			// cache multi-get
			$test->getListByIdsTest();
			$test->getListByIdsTest();
		}

		return $this;
	}
}
?>