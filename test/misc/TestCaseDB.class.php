<?php
	abstract class TestCaseDB extends TestCase
	{
		private $dBCreator = null;
		
		public function setUp()
		{
			parent::setUp();
			
			$this->dBCreator = DBTestCreator::create()->
				setSchemaPath(ONPHP_META_AUTO_DIR.'schema.php')->
				setTestPool(DBTestPool::me());
		}
		
		public function tearDown() {
			parent::tearDown();
		}
		
		/**
		 * @return DBTestCreator
		 */
		protected function getDBCreator() {
			return $this->dBCreator;
		}
		
		/**
		 * @param type $type
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