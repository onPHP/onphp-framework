<?php
	namespace Onphp\Test;

	final class CriteriaTest extends TestCase
	{
		public function testClassProjection()
		{
			$criteria =
				\Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::chain()->add(
						\Onphp\ClassProjection::create('\Onphp\Test\TestUser')
					)->
					add(
						\Onphp\Projection::group('id')
					)
				);
			
			$this->assertEquals(
				$criteria->toSelectQuery()->getFieldsCount(),
				count(TestUser::dao()->getFields())
			);
		}
		
		public function testAddProjection()
		{
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertEquals(
				$criteria->getProjection(),
				\Onphp\Projection::chain()
			);
			
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				addProjection(
					\Onphp\Projection::chain()
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				\Onphp\Projection::chain()
			);
			
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				addProjection(
					\Onphp\Projection::property('id')
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::property('id')
					)
			);
		}
		
		public function testSetProjection()
		{
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::chain()->
						add(
							\Onphp\Projection::property('id')
						)
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::property('id')
					)
			);
			
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				);
			
			$this->assertEquals(
				$criteria->getProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::property('id')
					)
			);
		}
		
		/**
		 * @dataProvider orderDataProvider
		**/
		public function testOrder($order, $expectedString)
		{
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				addOrder($order);
			
			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user.id FROM test_user ORDER BY '.$expectedString
			);
		}

		public function testValueObject()
		{
			$criteria =
				\Onphp\Criteria::create(TestUserWithContact::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::eq('contacts.city', 1)
				);			

			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user_with_contact.id FROM test_user_with_contact WHERE (test_user_with_contact.city_id = 1)'
			);

			$criteria =
				\Onphp\Criteria::create(TestUserWithContact::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::eq('contacts.city.name', 'Moscow')
				);		

			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user_with_contact.id FROM test_user_with_contact JOIN custom_table AS 3524772f_city_id ON (test_user_with_contact.city_id = 3524772f_city_id.id) WHERE (3524772f_city_id.name = Moscow)'
			);

			//check extending ValueObject
			$criteria =
				\Onphp\Criteria::create(TestUserWithContactExtended::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::eq('contactExt.skype', 'skype_nick_name')
				);

			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user_with_contact_extended.id FROM test_user_with_contact_extended WHERE (test_user_with_contact_extended.skype = skype_nick_name)'
			);

			$criteria =
				\Onphp\Criteria::create(TestUserWithContactExtended::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::eq('contactExt.city.name', 'Moscow')
				);

			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user_with_contact_extended.id FROM test_user_with_contact_extended JOIN custom_table AS 3524772f_city_id ON (test_user_with_contact_extended.city_id = 3524772f_city_id.id) WHERE (3524772f_city_id.name = Moscow)'
			);
		}

		public function testDialectStringObjects()
		{
			$criteria =
				\Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::gt('registered', \Onphp\Date::create('2011-01-01'))
				);
			
			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user.id FROM test_user WHERE (test_user.registered > 2011-01-01)'
			);
			
			$criteria =
				\Onphp\Criteria::create(TestUserWithContactExtended::dao())->
				setProjection(
					\Onphp\Projection::property('contactExt.city.id', 'cityId')
				)->
				add(
					\Onphp\Expression::eq('contactExt.city', TestCity::create()->setId(22))
				);
			
			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user_with_contact_extended.city_id AS cityId FROM test_user_with_contact_extended WHERE (test_user_with_contact_extended.city_id = 22)'
			);
			
			$cityList = array(
				TestCity::create()->setId(3),
				TestCity::create()->setId(44),
			);
			
			$criteria =
				\Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::in('city', $cityList)
				);
			
			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT test_user.id FROM test_user WHERE (test_user.city_id IN (3, 44))'
			);
		}
		
		public function testSqlFunction()
		{
			$criteria = \Onphp\Criteria::create(TestCity::dao())->
				addProjection(
					\Onphp\Projection::property(
						\Onphp\SQLFunction::create(
							'count',
							\Onphp\SQLFunction::create(
								'substring',
								\Onphp\BinaryExpression::create(
									'name',
									\Onphp\BinaryExpression::create(
										\Onphp\DBValue::create('M....w'),
										\Onphp\DBValue::create('#'),
										'for'
									)->
									noBrackets(),
									'from'
								)->
								noBrackets()
							)
						)->
						setAggregateDistinct()->
						setAlias('my_alias')
					)
				);
			
			$this->assertEquals(
				$criteria->toDialectString(\Onphp\ImaginaryDialect::me()),
				'SELECT count(DISTINCT substring(custom_table.name from M....w for #)) AS my_alias FROM custom_table'
			);
		}
		
		public function testSleepWithEmptyDao()
		{
			$baseCriteria =
				\Onphp\Criteria::create()->
				setLimit(10);
			
			$newBaseCriteria =
				unserialize(serialize($baseCriteria));
			
			$this->assertEquals(
				$newBaseCriteria->getLimit(),
				$baseCriteria->getLimit()
			);
			
			$this->assertEquals(
				$newBaseCriteria->getDao(),
				$baseCriteria->getDao()
			);
		}
		
		public function testForgottenDao()
		{
			$criteria =
				\Onphp\Criteria::create()->
				add(\Onphp\Expression::eq('id', 42));
			
			$listCriteria = clone $criteria;
			
			try {
				$listCriteria->getList();
				
				$this->fail();
			} catch (\Onphp\WrongStateException $e) {/*it's good*/}
			
			$customCriteria = clone $criteria;
			
			try {
				$customCriteria->
					addProjection(\Onphp\Projection::property('id'))->
					getCustomList();
				
				$this->fail();
			} catch (\Onphp\WrongStateException $e) {/*it's good*/}
		}

		public static function orderDataProvider()
		{
			return array(
				array(\Onphp\OrderBy::create('id'), 'test_user.id'),
				array(
					\Onphp\OrderChain::create()->
						add(\Onphp\OrderBy::create('id')->asc())->
						add(\Onphp\OrderBy::create('id')),
					'test_user.id ASC, test_user.id'
				),
				array(\Onphp\OrderBy::create('id')->asc(), 'test_user.id ASC'),
				array(\Onphp\OrderBy::create('id')->desc(), 'test_user.id DESC'),
				array(\Onphp\OrderBy::create('id')->nullsFirst(), 'test_user.id NULLS FIRST'),
				array(\Onphp\OrderBy::create('id')->nullsLast(), 'test_user.id NULLS LAST'),
				array(\Onphp\OrderBy::create('id')->asc()->nullsLast(), 'test_user.id ASC NULLS LAST'),
				array(\Onphp\OrderBy::create('id')->desc()->nullsFirst(), 'test_user.id DESC NULLS FIRST'),
				array(
					\Onphp\OrderBy::create(\Onphp\Expression::isNull('id'))->
						asc()->
						nullsFirst(),
					'((test_user.id IS NULL)) ASC NULLS FIRST'
				)
			);
		}
	}
?>