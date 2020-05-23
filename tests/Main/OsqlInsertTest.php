<?php
	
namespace OnPHP\Tests\Main;

use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBValue;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Tests\TestEnvironment\TestCaseDB;

/**
 * @group core
 * @group db
 * @group osql
 * @group postgresql
 */
final class OsqlInsertTest extends TestCaseDB
{
	public function testInsertFromSelect()
	{
		$dialect = $this->getDbByType(PgSQL::class)->getDialect();

		$select = OSQL::select()->
			from('test_table2')->
			get('field3')->
			get('field_7')->
			andWhere(Expression::gt('field2', DBValue::create('33')));

		$insert = OSQL::insert()->
			setSelect($select)->
			into('test_table')->
			set('field2', 2)->
			set('field16', 3);

		$this->assertEquals(
			$insert->toDialectString($dialect),
			'INSERT INTO "test_table" ("field2", "field16") ('
			.'SELECT "test_table2"."field3", "test_table2"."field_7" '
			.'FROM "test_table2" WHERE ("field2" > \'33\')'
			.')'
		);
	}
}
?>