<?php
	abstract class TestCaseDAO extends TestCaseDB
	{
		public function setUp()
		{
			parent::setUp();
			
			$this->getDBCreator()->dropDB(true)->createDB();
		}
	}
?>