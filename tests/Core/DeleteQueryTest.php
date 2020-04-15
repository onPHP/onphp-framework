<?php
/* $Id$ */

namespace OnPHP\Tests\Core;

use OnPHP\Core\DB\ImaginaryDialect;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Tests\TestEnvironment\TestCase;


final class DeleteQueryTest extends TestCase
{
	public function testQuery()
	{
		$query = OSQL::delete()->from('pity_table');
		
		$dialect = ImaginaryDialect::me();
		
		try {
			$query->toDialectString($dialect);
			$this->fail();
		} catch (WrongArgumentException $e) {
			/* pass */
		}
		
		$query->where(Expression::eq(1, 2));
		
		$this->assertEquals(
			$query->toDialectString($dialect),
			'DELETE FROM pity_table WHERE (1 = 2)'
		);
		
		$query->andWhere(Expression::notEq('a', 'b'));
		
		$this->assertEquals(
			$query->toDialectString($dialect),
			'DELETE FROM pity_table WHERE (1 = 2) AND (a != b)'
		);
	}
}
?>