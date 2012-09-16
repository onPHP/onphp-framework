<?php
	/* $Id$ */
	
	final class TruncateQueryTest extends TestCaseDB
	{
		public function testQuery()
		{
			$pgDialect = $this->getDbByType('PgSQL')->getDialect();
			$myDialect = $this->getDbByType('MySQL')->getDialect();
			$liteDialect = $this->getDbByType('SQLitePDO')->getDialect();
			
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
				$query->toDialectString($pgDialect),
				'TRUNCATE TABLE "single_table";'
			);
			
			$this->assertEquals(
				$query->toDialectString($liteDialect),
				'DELETE FROM "single_table";'
			);
			
			$this->assertEquals(
				$query->toDialectString($myDialect),
				'TRUNCATE TABLE `single_table`;'
			);
			
			$query = OSQL::truncate(array('foo', 'bar', 'bleh'));
			
			$this->assertEquals(
				$query->toDialectString(ImaginaryDialect::me()),
				'DELETE FROM foo; DELETE FROM bar; DELETE FROM bleh;'
			);
			
			$this->assertEquals(
				$query->toDialectString($pgDialect),
				'TRUNCATE TABLE "foo", "bar", "bleh";'
			);
			
			$this->assertEquals(
				$query->toDialectString($liteDialect),
				'DELETE FROM "foo"; DELETE FROM "bar"; DELETE FROM "bleh";'
			);

			$this->assertEquals(
				$query->toDialectString($myDialect),
				'TRUNCATE TABLE `foo`; TRUNCATE TABLE `bar`; TRUNCATE TABLE `bleh`;'
			);
		}
	}
?>