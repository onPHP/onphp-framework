<?php
	
	namespace Onphp\Test;

	final class OsqlDeleteTest extends TestCaseDB
	{
		public function testQuery()
		{
			$query = \Onphp\OSQL::delete()->from('pity_table');
			
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			try {
				$query->toDialectString($dialect);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
			
			$query->where(\Onphp\Expression::eq('count', 2));
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM "pity_table" WHERE ("count" = \'2\')'
			);
			
			$query->andWhere(\Onphp\Expression::notEq('a', '2'));
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM "pity_table" WHERE ("count" = \'2\') AND ("a" != \'2\')'
			);
		}
	}
?>