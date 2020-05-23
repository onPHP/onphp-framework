<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Tests\Meta\Business\TestLazy;
use OnPHP\Tests\Meta\Business\TestStringIdentifier;
use OnPHP\Tests\Meta\Business\TestStringIdentifierRelated;
use OnPHP\Tests\Meta\Business\TestUser;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

/**
 * @group core
 * @group db
 * @group dao
 */
class IdDBTest extends TestCaseDAO
{
	public function testGetByEmptyId()
	{
		foreach (DBTestPool::me()->getPool() as $db) {
			DBPool::me()->setDefault($db);
			$this->getByEmptyIdTest(0);
			$this->getByEmptyIdTest(null);
			$this->getByEmptyIdTest('');
			$this->getByEmptyIdTest('0');
			$this->getByEmptyIdTest(false);

			$empty = TestLazy::create();

			$this->assertNull($empty->getCity());
			$this->assertNull($empty->getCityOptional());
			$this->assertNull($empty->getEnum());
			$this->assertNull($empty->getStaticEnum());
		}
	}

	public function testStringIdentifier()
	{
		$identifier =
			TestStringIdentifier::proto()->getPropertyByName('id');

		$this->assertEquals($identifier->getType(), 'scalarIdentifier');

		$identifier =
			TestStringIdentifierRelated::proto()->getPropertyByName('test');

		$this->assertEquals($identifier->getType(), 'scalarIdentifier');
	}
	
	private function getByEmptyIdTest($id)
	{
		try {
			TestUser::dao()->getById($id);
			$this->fail();
		} catch (WrongArgumentException $e) {
			// pass
		}
	}
}
?>