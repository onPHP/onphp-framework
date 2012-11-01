<?php
	
	namespace Onphp\Test;

	final class OsqlReturningTest extends TestCaseDB
	{
		public function testUpdate()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$query = \Onphp\OSQL::update('test_table')->
				set('field1', 1)->
				where(\Onphp\Expression::eq('field1',2));
			
			$this->addReturning($query);
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'UPDATE "test_table" '
				.'SET "field1" = \'1\' '
				.'WHERE ("field1" = \'2\') '
				.'RETURNING '
					.'"test_table"."field1" AS "alias1", '
					.'"test_table"."field2", '
					.'count("test_table"."field5") AS "alias5", '
					.'(SELECT "test_table1"."id" FROM "test_table1") AS "foo1"'
			);
		}
		
		public function testInsert()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$query = \Onphp\OSQL::insert()->
				into('test_table')->
				set('field2', 2)->
				set('field16', 3);
			
			$this->addReturning($query);
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'INSERT INTO "test_table" ("field2", "field16") '
				.'VALUES (\'2\', \'3\') '
				.'RETURNING '
					.'"test_table"."field1" AS "alias1", '
					.'"test_table"."field2", '
					.'count("test_table"."field5") AS "alias5", '
					.'(SELECT "test_table1"."id" FROM "test_table1") AS "foo1"'
			);
		}
		
		public function testDelete()
		{
			$query = \Onphp\OSQL::delete()->from('pity_table');
			
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			try {
				$query->toDialectString($dialect);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				//pass
			}
			
			$query->where(\Onphp\Expression::eq('count', 2))->returning('id');
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM "pity_table" WHERE ("count" = \'2\') RETURNING "pity_table"."id"'
			);
		}
		
		public function testHasNoReturning()
		{
			$dialect = \Onphp\ImaginaryDialect::me();
			
			$query = \Onphp\OSQL::update('test_table')->
				set('field1', 1)->
				where(\Onphp\Expression::eq('field1',2))->
				returning('field1');
			
			try {
				$query->toDialectString($dialect);
			} catch (\Onphp\UnimplementedFeatureException $e) {
				return $this;
			}
			
			$this->fail();
		}
		
		/**
		 * @return \Onphp\InsertOrUpdateQuery
		**/
		protected function addReturning(\Onphp\InsertOrUpdateQuery $query)
		{
			$query->
				returning(\Onphp\DBField::create('field1', 'test_table'), 'alias1')->
				returning('field2')->
				returning(
					\Onphp\SQLFunction::create(
						'count', \Onphp\DBField::create('field5', 'test_table')
					)->
					setAlias('alias5')
				)->
				returning(
					\Onphp\OSQL::select()->
						from('test_table1')->
						setName('foo1')->
						get('id')
				);
			
			return $query;
		}
	}
?>