<?php
	/* $Id$ */
	
	final class UnionTest extends UnitTestCase
	{
		private $singleUnion 		= null;
		private $singleUnionAll		= null;
		private $singleIntersect	= null;
		private $singleIntersectAll	= null;
		private $singleExcept		= null;
		private $singleExceptAll	= null;
		private $blockUnion			= null;
		private $blockUnionAll		= null;
		
		public function setUp()
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
		}
			
		public function testPostgresql()
		{
			$dialect = PostgresDialect::me();
			
			$this->assertEqual(
				$this->singleUnion->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			$this->assertEqual(
				$this->singleUnionAll->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			
			$this->assertEqual(
				$this->singleIntersect->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" INTERSECT SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			$this->assertEqual(
				$this->singleIntersectAll->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" INTERSECT ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			
			$this->assertEqual(
				$this->singleExcept->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" EXCEPT SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			$this->assertEqual(
				$this->singleExceptAll->toDialectString($dialect),
				'SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" EXCEPT ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable"'
			);
			
			$this->assertEqual(
				$this->blockUnion->toDialectString($dialect),
				'(SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION SELECT "middleTable"."a", "middleTable"."c" FROM "middleTable" UNION SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable")'
			);
			$this->assertEqual(
				$this->blockUnionAll->toDialectString($dialect),
				'(SELECT "leftTable"."a", "leftTable"."b" AS "c" FROM "leftTable" UNION ALL SELECT "middleTable"."a", "middleTable"."c" FROM "middleTable" UNION ALL SELECT "rightTable"."d" AS "a", "rightTable"."c" FROM "rightTable")'
			);
		}
	}
?>