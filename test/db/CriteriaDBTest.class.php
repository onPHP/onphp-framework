<?php
	namespace Onphp\Test;

	class CriteriaDBTest extends TestCaseDAO
	{
		public function testCriteria()
		{
			foreach (DBTestPool::me()->iterator() as $db) {
				$this->getDBCreator()->fillDB();

				$queryResult = \Onphp\Criteria::create(TestCity::dao())->getResult();
				$this->assertEquals(2, $queryResult->getCount());

				\Onphp\Cache::me()->clean();
			}
		}
	}
?>