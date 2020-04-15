<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\Cache\Cache;
use OnPHP\Core\DB\DB;
use OnPHP\Core\DB\DBPool;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

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