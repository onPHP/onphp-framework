<?php
	
	namespace Onphp\Test;

	final class OsqlSelectTest extends TestCaseDB
	{
		public function testSelectGet()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$query = \Onphp\OSQL::select()->
				from('test_table')->
				get(\Onphp\DBField::create('field1', 'test_table'), 'alias1')->
				get(\Onphp\DBField::create('field2', 'test_table'))->
				get('field3', 'alias3')->
				get('field4')->
				get(
					\Onphp\SQLFunction::create(
						'count', \Onphp\DBField::create('field5', 'test_table')
					)->
					setAggregateDistinct()->
					setAlias('alias5')
				)->
				get(
					\Onphp\SQLFunction::create(
						'substring',
						\Onphp\BinaryExpression::create(
							\Onphp\DBField::create('field6', 'test_table'),
							\Onphp\DBValue::create('a..b'),
							'from'
						)->
						noBrackets()
					)
				);
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'SELECT '
					.'"test_table"."field1" AS "alias1", '
					.'"test_table"."field2", '
					.'"test_table"."field3" AS "alias3", '
					.'"test_table"."field4", '
					.'count(DISTINCT "test_table"."field5") AS "alias5", '
					.'substring("test_table"."field6" from \'a..b\') '
				.'FROM "test_table"'
			);
		}
		
		public function testSelectSubqueryGet()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$query = \Onphp\OSQL::select()->
				from('test_table')->
				get('field1')->
				get(
					\Onphp\OSQL::select()->
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
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();

			$joinTypeList = array(
				'JOIN ' => 'join',
				'LEFT JOIN ' => 'leftJoin',
				'RIGHT JOIN ' => 'rightJoin',
				'FULL OUTER JOIN ' => 'fullOuterJoin'
			);

			$joinExpression =
				\Onphp\Expression::eq(
					\Onphp\DBField::create('joinField', 'table1'),
					\Onphp\DBField::create('joinField', 'table2')
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
				\Onphp\OSQL::select()->
				from('table1')->
				get(\Onphp\DBField::create('field1', 'table1'))->
				get(\Onphp\DBField::create('field2', 'table2'));
		}

	}
?>