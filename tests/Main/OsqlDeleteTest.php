<?php
	
namespace OnPHP\Tests\Main;

use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Tests\TestEnvironment\TestCaseDB;

final class OsqlDeleteTest extends TestCaseDB
{
	public function testQuery()
	{
		$query = OSQL::delete()->from('pity_table');

		$dialect = $this->getDbByType(PgSQL::class)->getDialect();

		try {
			$query->toDialectString($dialect);
			$this->fail();
		} catch (WrongArgumentException $e) {
			/* pass */
		}

		$query->where(Expression::eq('count', 2));

		$this->assertEquals(
			$query->toDialectString($dialect),
			'DELETE FROM "pity_table" WHERE ("count" = \'2\')'
		);

		$query->andWhere(Expression::notEq('a', '2'));

		$this->assertEquals(
			$query->toDialectString($dialect),
			'DELETE FROM "pity_table" WHERE ("count" = \'2\') AND ("a" != \'2\')'
		);
	}
}
?>