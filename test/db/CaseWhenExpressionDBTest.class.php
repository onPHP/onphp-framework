<?php
	class CaseWhenExpressionDBTest extends TestCaseDAO
	{
		/**
		 * @group caseWhen
		 */
		public function testCaseWhenExpression()
		{
			$dialect = $this->getDbByType('PgSQL')->getDialect();

			$beautifulNameExpr = CaseWhenExpression::create(
				Expression::isTrue('capital'),
				SQLFunction::create('upper', 'name'),
				SQLFunction::create('lower', 'name')
			)
				->addCase(Expression::eq('name', 'St. Peterburg'), 'ST. PETERBURG');

			$orderExpr = CaseWhenExpression::create()
				->addCase(Expression::isTrue('capital'), SQLFunction::create('upper', 'name'))
				->addCase(Expression::eq('name', 'St. Peterburg'), 'ST. PETERBURG')
				->addElse('name');

			$criteria = Criteria::create(TestCity::dao())
				->addProjection(Projection::property($beautifulNameExpr, 'beautifulName'))
				->addOrder($orderExpr);

			$expectation = 'SELECT CASE '
					.'WHEN ("custom_table"."capital" IS TRUE) THEN upper("custom_table"."name") '
					.'WHEN ("custom_table"."name" = \'St. Peterburg\') THEN \'ST. PETERBURG\' '
					.'ELSE lower("custom_table"."name") '
				.'END AS "beautifulName" '
				.'FROM "custom_table" '
				.'ORDER BY CASE '
					.'WHEN ("custom_table"."capital" IS TRUE) THEN upper("custom_table"."name") '
					.'WHEN ("custom_table"."name" = \'St. Peterburg\') THEN \'ST. PETERBURG\' '
					.'ELSE "custom_table"."name" '
				.'END';

			$this->assertEquals($expectation, $criteria->toDialectString($dialect));
		}
	}
?>