<?php
	namespace Onphp\Test;

	abstract class TestTables extends TestCase
	{
		protected $schema = null;
		
		public function __construct()
		{
			require ONPHP_META_AUTO_DIR.'schema.php';
			
			\Onphp\Assert::isTrue(isset($schema));
			
			$this->schema = $schema;
			
			// in case of unclean shutdown of previous tests
			foreach (DBTestPool::me()->getPool() as $name => $db) {
				foreach ($this->schema->getTableNames() as $name) {
					try {
						$db->queryRaw(
							OSQL::dropTable($name, true)->toDialectString(
								$db->getDialect()
							)
						);
					} catch (\Onphp\DatabaseException $e) {
						// ok
					}
					
					if ($db->hasSequences()) {
						foreach (
							$this->schema->getTableByName($name)->getColumns()
								as $columnName => $column
						)
						{
							try {
								if ($column->isAutoincrement())
									$db->queryRaw("DROP SEQUENCE {$name}_id;");
							} catch (\Onphp\DatabaseException $e) {
								// ok
							}
						}
					}
				}
			}
		}
		
		public function create()
		{
			$pool = DBTestPool::me()->getPool();
			
			foreach ($pool as $name => $db) {
				foreach ($this->schema->getTables() as $name => $table) {
					$db->queryRaw($table->toDialectString($db->getDialect()));
				}
			}
			
			return $this;
		}
		
		public function drop()
		{
			$pool = DBTestPool::me()->getPool();
			
			foreach ($pool as $name => $db) {
				foreach ($this->schema->getTableNames() as $name) {
					$db->queryRaw(
						OSQL::dropTable($name, true)->toDialectString(
							$db->getDialect()
						)
					);
					
					if ($db->hasSequences()) {
						foreach (
							$this->schema->getTableByName($name)->getColumns()
								as $columnName => $column)
						{
							if ($column->isAutoincrement())
								$db->queryRaw("DROP SEQUENCE {$name}_id;");
						}
					}
				}
			}
			
			return $this;
		}

		protected function setUp()
		{
			if (!DBTestPool::me()->getPool())
				$this->markTestSkipped('haven\'t connected database pool');
		}
	}
?>