<?php
	/* $Id$ */
	
	final class CriteriaTest extends TestCase
	{
		public function testClassProjection()
		{
			$criteria =
				Criteria::create(TestUser::dao())->
				setProjection(
					Projection::chain()->add(
						ClassProjection::create('TestUser')
					)->
					add(
						Projection::group('id')
					)
				);
			
			$this->assertEquals(
				$criteria->toSelectQuery()->getFieldsCount(),
				count(TestUser::dao()->getFields())
			);
		}
	}
?>