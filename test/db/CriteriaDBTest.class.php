<?php
	class CriteriaDBTest extends TestCaseDAO
	{
		public function testCriteria()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				/* @var $db DB */
				DBPool::me()->setDefault($db);
				$this->getDBCreator()->fillDB();

				$queryResult = Criteria::create(TestCity::dao())->getResult();
				$this->assertEquals(2, $queryResult->getCount());

				Cache::me()->clean();
			}
		}
	}
?>