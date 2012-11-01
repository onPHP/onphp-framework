<?php
	namespace Onphp\Test;

	class CriteriaDBTest extends TestCaseDAO
	{
		public function testCriteria()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				/* @var $db \Onphp\DB */
				\Onphp\DBPool::me()->setDefault($db);
				$this->getDBCreator()->fillDB();

				$queryResult = \Onphp\Criteria::create(TestCity::dao())->getResult();
				$this->assertEquals(2, $queryResult->getCount());

				\Onphp\Cache::me()->clean();
			}
		}
	}
?>