<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB\DB;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDB;

/**
 * @group core
 * @group db
 * @group postgresql
 */
class PgSQLFullTextSearchDBTest extends TestCaseDB
{
	/**
	 * @test
	 */
	public function getPgSQL()
	{
		foreach (DBTestPool::me()->getPool() as $db)
			if ($db instanceof PgSQL)
				return $db;
			
		$this->markTestIncomplete('Required PgSQL for testing');
	}
	
	/**
	 * @depends getPgSQL
	 * @return PgSQL
	 */
	public function testPrepairFullText(PgSQL $db)
	{
		$this->assertEquals(
			"'новый год' & 'снегурочка' & 'праздник'",
			$db->getDialect()->prepareFullText(
				array('Новый год', 'Снегурочка', 'ПрАзДнИк'),
				DB::FULL_TEXT_AND
			)
		);
	}
}
?>