<?php
	/* $Id$ */
	
	final class TruncateQueryTest extends UnitTestCase
	{
		public function testQuery()
		{
			$query = OSQL::truncate(array('foo', 'bar', 'bleh'));
			
			$this->assertEqual(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM foo; DELETE FROM bar; DELETE FROM bleh;'
			);
			
			$this->assertEqual(
				$query->toDialectString(PostgresDialect::me()),
				'TRUNCATE TABLE foo, bar, bleh;'
			);
			
			$this->assertEqual(
				$query->toDialectString(LiteDialect::me()),
				'DELETE FROM foo; DELETE FROM bar; DELETE FROM bleh;'
			);

			$this->assertEqual(
				$query->toDialectString(MyDialect::me()),
				'TRUNCATE TABLE foo; TRUNCATE TABLE bar; TRUNCATE TABLE bleh;'
			);
		}
	}
?>