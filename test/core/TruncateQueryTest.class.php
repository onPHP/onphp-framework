<?php
	/* $Id$ */
	
	final class TruncateQueryTest extends TestCase
	{
		public function testQuery()
		{
			$query = OSQL::truncate('single_table');
			
			try {
				OSQL::truncate()->toDialectString(ImaginaryDialect::me());
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
			
			$this->assertEquals(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM single_table;'
			);
			
			$this->assertEquals(
				$query->toDialectString(PostgresDialect::me()),
				'TRUNCATE TABLE "single_table";'
			);
			
			$this->assertEquals(
				$query->toDialectString(LiteDialect::me()),
				'DELETE FROM "single_table";'
			);
			
			$this->assertEquals(
				$query->toDialectString(MyDialect::me()),
				'TRUNCATE TABLE `single_table`;'
			);
			
			$query = OSQL::truncate(array('foo', 'bar', 'bleh'));
			
			$this->assertEquals(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM foo; DELETE FROM bar; DELETE FROM bleh;'
			);
			
			$this->assertEquals(
				$query->toDialectString(PostgresDialect::me()),
				'TRUNCATE TABLE "foo", "bar", "bleh";'
			);
			
			$this->assertEquals(
				$query->toDialectString(LiteDialect::me()),
				'DELETE FROM "foo"; DELETE FROM "bar"; DELETE FROM "bleh";'
			);

			$this->assertEquals(
				$query->toDialectString(MyDialect::me()),
				'TRUNCATE TABLE `foo`; TRUNCATE TABLE `bar`; TRUNCATE TABLE `bleh`;'
			);
		}
	}
?>