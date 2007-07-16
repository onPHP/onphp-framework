<?php
	/* $Id$ */
	
	final class DeleteQueryTest extends UnitTestCase
	{
		public function testQuery()
		{
			$query = OSQL::delete()->from('pity_table');
			
			$dialect = ImaginaryDialect::me();
			
			try {
				$query->toDialectString($dialect);
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
			
			$query->where(Expression::eq(1, 2));
			
			$this->assertEqual(
				$query->toDialectString($dialect),
				'DELETE FROM pity_table WHERE (1 = 2)'
			);
			
			$query->andWhere(Expression::notEq('a', 'b'));
			
			$this->assertEqual(
				$query->toDialectString($dialect),
				'DELETE FROM pity_table WHERE (1 = 2) AND (a != b)'
			);
		}
	}
?>