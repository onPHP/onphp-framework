<?php
	/* $Id$ */
	
	final class TruncateQueryTest extends UnitTestCase
	{
		public function testQuery()
		{
			$query = OSQL::truncate('single_table');
			
			try {
				OSQL::truncate()->toDialectString(ImaginaryDialect::me());
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
			
			$this->assertEqual(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM single_table;'
			);
			
			$this->assertEqual(
				$query->toDialectString(PostgresDialect::me()),
				'TRUNCATE TABLE "single_table";'
			);
			
			$this->assertEqual(
				$query->toDialectString(LiteDialect::me()),
				'DELETE FROM "single_table";'
			);
			
			$this->assertEqual(
				$query->toDialectString(MyDialect::me()),
				'TRUNCATE TABLE `single_table`;'
			);
			
			$query = OSQL::truncate(array('foo', 'bar', 'bleh'));
			
			$this->assertEqual(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM foo; DELETE FROM bar; DELETE FROM bleh;'
			);
			
			$this->assertEqual(
				$query->toDialectString(PostgresDialect::me()),
				'TRUNCATE TABLE "foo", "bar", "bleh";'
			);
			
			$this->assertEqual(
				$query->toDialectString(LiteDialect::me()),
				'DELETE FROM "foo"; DELETE FROM "bar"; DELETE FROM "bleh";'
			);

			$this->assertEqual(
				$query->toDialectString(MyDialect::me()),
				'TRUNCATE TABLE `foo`; TRUNCATE TABLE `bar`; TRUNCATE TABLE `bleh`;'
			);
		}
	}
?>