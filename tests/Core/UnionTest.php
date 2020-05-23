<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\OSQL\CombineQuery;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Tests\TestEnvironment\TestCaseDB;

/**
 * @group core
 * @group db
 * @group osql
 * @group postgresql
 */
final class UnionTest extends TestCaseDB
{
	private $singleUnion 		= null;
	private $singleUnionAll		= null;
	private $singleIntersect	= null;
	private $singleIntersectAll	= null;
	private $singleExcept		= null;
	private $singleExceptAll	= null;
	private $blockUnion			= null;
	private $blockUnionAll		= null;
	private $limitedOrderedUnion = null;

	public function setUp() : void
	{
		$left = OSQL::select()->
			from('leftTable')->
			get('a')->
			get('b', 'c');

		$middle = OSQL::select()->
			from('middleTable')->
			get('a')->
			get('c');

		$right = OSQL::select()->
			from('rightTable')->
			get('d', 'a')->
			get('c');

		$this->singleUnion = CombineQuery::union($left, $right);
		$this->singleUnionAll = CombineQuery::unionAll($left, $right);

		$this->singleIntersect = CombineQuery::intersect($left, $right);
		$this->singleIntersectAll = CombineQuery::intersectAll(
			$left,
			$right
		);

		$this->singleExcept = CombineQuery::except($left, $right);
		$this->singleExceptAll = CombineQuery::exceptAll($left, $right);

		$this->blockUnion = CombineQuery::unionBlock(
			$left,
			$middle,
			$right
		);
		$this->blockUnionAll = CombineQuery::unionAllBlock(
			$left,
			$middle,
			$right
		);

		$this->limitedOrderedUnion = CombineQuery::union($left, $right)->
			orderBy('a')->
			limit(2, 3);
	}

	public function testPostgresql()
	{
		$dialect = $this->getDbByType(PgSQL::class)->getDialect();

		$this->assertEquals(
			$this->singleUnion->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);
		$this->assertEquals(
			$this->singleUnionAll->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);

		$this->assertEquals(
			$this->singleIntersect->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" INTERSECT SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);
		$this->assertEquals(
			$this->singleIntersectAll->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" INTERSECT ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);

		$this->assertEquals(
			$this->singleExcept->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" EXCEPT SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);
		$this->assertEquals(
			$this->singleExceptAll->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" EXCEPT ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
		);

		$this->assertEquals(
			$this->blockUnion->toDialectString($dialect),
			'(SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION SELECT "middleTable"."a", "middleTable"."c" FROM "middleTable" UNION SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable")'
		);
		$this->assertEquals(
			$this->blockUnionAll->toDialectString($dialect),
			'(SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION ALL SELECT "middleTable"."a", "middleTable"."c" FROM "middleTable" UNION ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable")'
		);

		$this->assertEquals(
			$this->limitedOrderedUnion->toDialectString($dialect),
			'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable" ORDER BY "a" LIMIT 2 OFFSET 3'
		);
	}
}
?>