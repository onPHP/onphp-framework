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
				$db->queryNull(
					$this->schema
				);
			}
		}
		
		public function drop()
		{
			$pool = DBTestPool::me()->getPool();
			
			$drop = new Queue();
			
			foreach ($this->schema->getTableNames() as $name) {
				$drop->add(OSQL::dropTable($name, true));
			}
			
			foreach ($pool as $name => $db) {
				$drop->run($db);
			}
		}
	}
?>