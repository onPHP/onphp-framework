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
		
		public function testAddProjection()
		{
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertEquals(
				$criteria->getProjection(),
				Projection::chain()
			);
			
			$criteria = Criteria::create(TestUser::dao())->
				addProjection(
					Projection::chain()
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				Projection::chain()
			);
			
			$criteria = Criteria::create(TestUser::dao())->
				addProjection(
					Projection::property('id')
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				Projection::chain()->
					add(
						Projection::property('id')
					)
			);
		}
		
		public function testSetProjection()
		{
			$criteria = Criteria::create(TestUser::dao())->
				setProjection(
					Projection::chain()->
						add(
							Projection::property('id')
						)
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				Projection::chain()->
					add(
						Projection::property('id')
					)
			);
			
			$criteria = Criteria::create(TestUser::dao())->
				setProjection(
					Projection::property('id')
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				Projection::chain()->
					add(
						Projection::property('id')
					)
			);
		}
	}
?>