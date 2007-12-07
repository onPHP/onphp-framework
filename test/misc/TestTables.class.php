<?php
	/* $Id$ */
	
	abstract class TestTables extends UnitTestCase
	{
		private $schema = null;
		
		public function __construct()
		{
			require ONPHP_META_AUTO_DIR.'schema.php';
			
			Assert::isTrue(isset($schema));
			
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
					} catch (DatabaseException $e) {
						// ok
					}
					
					if ($db->supportSequences()) {
						foreach (
							$this->schema->getTableByName($name)->getColumns()
								as $columnName => $column
						)
						{
							try {
								if ($column->isAutoincrement())
									$db->queryRaw("DROP SEQUENCE {$name}_id;");
							} catch (DatabaseException $e) {
								// ok
							}
						}
					}
				}
			}
		}
		
		protected function create()
		{
			$pool = DBTestPool::me()->getPool();

			foreach ($pool as $name => $db) {
				DBFactory::setDefaultInstance($db);
				foreach ($this->schema->getTables() as $name => $table) {
					$db->queryRaw($table->toDialectString($db->getDialect()));
				}
			}
		}
		
		protected function drop()
		{
			$pool = DBTestPool::me()->getPool();
			
			foreach ($pool as $name => $db) {
				DBFactory::setDefaultInstance($db);
				foreach ($this->schema->getTableNames() as $name) {
					$db->queryRaw(
						OSQL::dropTable($name, true)->toDialectString(
							$db->getDialect()
						)
					);
					
					if ($db->supportSequences()) {
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
		}
	}
?>