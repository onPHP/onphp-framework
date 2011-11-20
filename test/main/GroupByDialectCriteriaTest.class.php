<?php
	/* $Id$ */

	final class GroupByDialectCriteriaTest extends TestCase
	{
		public function testMyDialect()
		{
			$criteria =
				Criteria::create(TestUser::dao())->
				addProjection(
					Projection::property(
						SQLFunction::create(
							'date', 'strangeTime'
						),

						'st'
					)
				)->
				addProjection(
					Projection::group('st')
				);

			$this->assertEquals(
				$criteria->toDialectString(MyDialect::me()),
				'SELECT date(`test_user`.`strange_time`) AS `st` FROM `test_user` GROUP BY `st`'
			);

			$criteria =
				Criteria::create(TestUser::dao())->
				addProjection(
					Projection::property('strangeTime', 'st')
				)->
				addProjection(
					Projection::group('st')
				);

			$this->assertEquals(
				$criteria->toDialectString(MyDialect::me()),
				'SELECT `strange_time` AS `st` FROM `test_user` GROUP BY `st`'
			);

		}
	}
?>
