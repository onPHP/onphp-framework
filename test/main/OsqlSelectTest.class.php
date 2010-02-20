<?php
	
	final class OsqlSelectTest extends TestCase
	{
		public function testSelectGet()
		{
			$dialect = PostgresDialect::me();
			
			$query = OSQL::select()->
				from('test_table')->
				get(DBField::create('field1', 'test_table'), 'alias1')->
				get(DBField::create('field2', 'test_table'))->
				get('field3', 'alias3')->
				get('field4')->
				get(
					SQLFunction::create(
						'count', DBField::create('field5', 'test_table')
					)->
					setAlias('alias5')
				);
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'SELECT '
					.'"test_table"."field1" AS "alias1", '
					.'"test_table"."field2", '
					.'"test_table"."field3" AS "alias3", '
					.'"test_table"."field4", '
					.'count("test_table"."field5") AS "alias5" '
				.'FROM "test_table"'
			);
		}
		
		public function testSelectSubqueryGet()
		{
			$dialect = PostgresDialect::me();
			
			$query = OSQL::select()->
				from('test_table')->
				get('field1')->
				get(
					OSQL::select()->
						from('test_table1')->
						setName('foo1')->
						get('id')
				);
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'SELECT '
					.'"test_table"."field1", '
					.'(SELECT "test_table1"."id" FROM "test_table1") AS "foo1" '
				.'FROM "test_table"'
			);
		}
	}
?>