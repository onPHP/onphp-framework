<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class DeleteQueryTest extends TestCase
	{
		public function testQuery()
		{
			$query = \Onphp\OSQL::delete()->from('pity_table');
			
			$dialect = \Onphp\ImaginaryDialect::me();
			
			try {
				$query->toDialectString($dialect);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
			
			$query->where(\Onphp\Expression::eq(1, 2));
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM pity_table WHERE (1 = 2)'
			);
			
			$query->andWhere(\Onphp\Expression::notEq('a', 'b'));
			
			$this->assertEquals(
				$query->toDialectString($dialect),
				'DELETE FROM pity_table WHERE (1 = 2) AND (a != b)'
			);
		}
	}
?>