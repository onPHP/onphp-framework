<?php
	
	final class OsqlInsertTest extends TestCase
	{
		public function testInsertFromSelect()
		{
			$dialect = PostgresDialect::me();
			
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