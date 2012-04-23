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

		public function testSelectJoin()
		{
			$dialect = PostgresDialect::me();

			$joinTypeList = array(
				'JOIN ' => 'join',
				'LEFT JOIN ' => 'leftJoin',
				'RIGHT JOIN ' => 'rightJoin',
				'FULL OUTER JOIN ' => 'fullOuterJoin'
			);

			$joinExpression =
				Expression::eq(
					DBField::create('joinField', 'table1'),
					DBField::create('joinField', 'table2')
				);

			$baseRawQuery =
					'SELECT '
						.'"table1"."field1", '
						.'"table2"."field2" '
					.'FROM "table1" ';


			foreach ($joinTypeList as $sqlJoin => $method) {
				$query =
					$this->getBaseJoinSelect()->{$method}('table2', $joinExpression);

				$rawQuery =
					$baseRawQuery
					.$sqlJoin
					.'"table2" ON ("table1"."joinField" = "table2"."joinField")';

				$this->assertEquals(
					$rawQuery,
					$query->toDialectString($dialect)
				);

				$query =
					$this->getBaseJoinSelect()->{$method}(
						'table2',
						$joinExpression,
						'table2'
					);

				$rawQuery =
					$baseRawQuery
					.$sqlJoin
					.'"table2" AS "table2" '
					.'ON ("table1"."joinField" = "table2"."joinField")';

				$this->assertEquals(
					$rawQuery,
					$query->toDialectString($dialect)
				);
			}
		}

		private function getBaseJoinSelect()
		{
			return
				OSQL::select()->
				from('table1')->
				get(DBField::create('field1', 'table1'))->
				get(DBField::create('field2', 'table2'));
		}

	}
?>