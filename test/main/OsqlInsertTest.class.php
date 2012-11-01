<?php
	
	namespace Onphp\Test;

	final class OsqlInsertTest extends TestCaseDB
	{
		public function testInsertFromSelect()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$select = \Onphp\OSQL::select()->
				from('test_table2')->
				get('field3')->
				get('field_7')->
				andWhere(\Onphp\Expression::gt('field2', \Onphp\DBValue::create('33')));
			
			$insert = \Onphp\OSQL::insert()->
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