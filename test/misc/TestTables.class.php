<?php
	/* $Id$ */
	
	abstract class TestTables extends UnitTestCase
	{
		private $schema = null;
		
		public function __construct()
		{
			require
				ONPHP_TEST_PATH
				.'meta'.DIRECTORY_SEPARATOR
				.'Auto'.DIRECTORY_SEPARATOR
				.'AutoSchema.php';
			
			$this->schema = $schema;
		}
		
		public function create()
		{
			$pool = DBTestPool::me()->getPool();
			
			foreach ($pool as $name => $db) {
				foreach ($this->schema->getTables() as $name => $table) {
					$db->queryRaw($table->toString($db->getDialect()));
				}
			}
		}
		
		public function drop()
		{
			$pool = DBTestPool::me()->getPool();
			
			foreach ($pool as $name => $db) {
				foreach ($this->schema->getTableNames() as $name) {
					$db->queryRaw(
						OSQL::dropTable($name, true)->toString(
							$db->getDialect()
						)
					);
				}
			}
		}
	}
?>