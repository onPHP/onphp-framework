<?php
	namespace Onphp\Test;

	class DBTestCreator
	{
		/**
		 * @var \Onphp\DBSchema
		 */
		private $schema = null;
		/**
		 * @var \Onphp\Test\DBTestPool
		 */
		private $pool = null;
		
		/**
		 * @return \Onphp\Test\DBTestCreator
		 */
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @param string $path
		 * @return \Onphp\Test\DBTestCreator
		 */
		public function setSchemaPath($path)
		{
			require $path;
			\Onphp\Assert::isTrue(isset($schema));
			\Onphp\Assert::isInstance($schema, '\Onphp\DBSchema');
			$this->schema = $schema;
			return $this;
		}
		
		/**
		 * @param \Onphp\Test\DBTestPool $testPool
		 * @return \Onphp\Test\DBTestCreator
		 */
		public function setTestPool(DBTestPool $testPool) {
			$this->pool = $testPool;
			return $this;
		}
		
		/**
		 * @return \Onphp\Test\DBTestCreator
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
			
			foreach ($this->pool->iterator() as $db) {
				foreach ($this->schema->getTables() as $table) {
					$db->queryRaw($table->toDialectString($db->getDialect()));
				}
			}
			
			return $this;
		}
		
		/**
		 * @param bool $clean
		 * @return \Onphp\Test\DBTestCreator
		 * @throws \Onphp\DatabaseException
		 */
		public function dropDB($clean = false)
		{
			foreach ($this->pool->iterator() as $db) {
				/* @var $db \Onphp\DB */
				foreach ($this->schema->getTableNames() as $name) {
					try {
						$db->queryRaw(
							\Onphp\OSQL::dropTable($name, true)->toDialectString(
								$db->getDialect()
							)
						);
					} catch (\Onphp\DatabaseException $e) {
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
							} catch (\Onphp\DatabaseException $e) {
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
		 * @param \Onphp\Test\TestCase $test
		 * @return \Onphp\Test\DBTestCreator
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
					\Onphp\Timestamp::create(time())
				)->
				setRegistered(
					\Onphp\Timestamp::create(time())->modify('-1 day')
				);
			
			$postgreser = clone $mysqler;
			
			$postgreser->
				setCredentials(
					Credentials::create()->
					setNickName('postgreser')->
					setPassword(sha1('postgreser'))
				)->
				setCity($piter)->
				setUrl(\Onphp\HttpUrl::create()->parse('http://postgresql.org/'));
			
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
				
				if ($test instanceof TestCaseDAO) {
					$test->getListByIdsTest();
				}
				
				\Onphp\Cache::me()->clean();
				
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
				} catch (\Onphp\ObjectNotFoundException $e) {
					/* pass */
				}
				
				$result =
					\Onphp\Criteria::create(TestUser::dao())->
					add(\Onphp\Expression::eq(1, 2))->
					getResult();
				
				$test->assertEquals($result->getCount(), 0);
				$test->assertEquals($result->getList(), array());
			}
			
			TestUser::dao()->import($firstClone);
			TestUser::dao()->import($secondClone);
			
			if ($test && $test instanceof TestCaseDAO) {
				// cache multi-get
				$test->getListByIdsTest();
				$test->getListByIdsTest();
			}
			
			return $this;
		}
	}
?>