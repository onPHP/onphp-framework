<?php
	
	final class OsqlReturningTest extends TestCase
	{
		public function testUpdate()
		{
			$dialect = PostgresDialect::me();
			
			$query = OSQL::update('test_table')->
				set('field1', 1)->
				where(Expression::eq('field1',2));
			
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
			$dialect = PostgresDialect::me();
			
			$query = OSQL::insert()->
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
			$query = OSQL::delete()->from('pity_table');
			
			$dialect = PostgresDialect::me();
			
			try {
				$query->toDialectString($dialect);
				$this->fail();
			} catch (WrongArgumentException $e) {
				//pass
			}
			
			$query->where(Expression::eq('count', 2))->returning('id');
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM "pity_table" WHERE ("count" = \'2\') RETURNING "pity_table"."id"'
			);
		}
		
		public function testHasNoReturning()
		{
			$dialect = MyDialect::me();
			
			$query = OSQL::update('test_table')->
				set('field1', 1)->
				where(Expression::eq('field1',2))->
				returning('field1');
			
			try {
				$query->toDialectString($dialect);
			} catch (UnimplementedFeatureException $e) {
				return $this;
			}
			
			$this->fail();
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		protected function addReturning(InsertOrUpdateQuery $query)
		{
			$query->
				returning(DBField::create('field1', 'test_table'), 'alias1')->
				returning('field2')->
				returning(
					SQLFunction::create(
						'count', DBField::create('field5', 'test_table')
					)->
					setAlias('alias5')
				)->
				returning(
					OSQL::select()->
						from('test_table1')->
						setName('foo1')->
						get('id')
				);
			
			return $query;
		}
	}
?>