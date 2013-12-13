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
			foreach (DBTestPool::me()->iterator() as $db) {
				foreach ($this->schema->getTableNames() as $name) {
					try {
						$db->queryRaw(
							\Onphp\OSQL::dropTable($name, true)->toDialectString(
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
			foreach (DBTestPool::me()->iterator() as $db) {
				foreach ($this->schema->getTables() as $table) {
					$db->queryRaw($table->toDialectString($db->getDialect()));
				}
			}
			
			return $this;
		}
		
		public function drop()
		{
			foreach (DBTestPool::me()->iterator() as $db) {
				foreach ($this->schema->getTableNames() as $name) {
					$db->queryRaw(
						\Onphp\OSQL::dropTable($name, true)->toDialectString(
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
			foreach (DBTestPool::me()->iterator() as $db) {
				return;
			}
			$this->markTestSkipped('haven\'t connected database pool');
		}
	}
?>