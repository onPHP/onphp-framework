<?php

	namespace Onphp\Test;

	use Onphp\Criteria;
	use Onphp\DBValue;
	use Onphp\Expression;
	use Onphp\ImaginaryDialect;
	use Onphp\TaggableSmartHandler;

	final class TaggableSmartHandlerTest extends TestCase
	{
		public function testTableLazyList()
		{
			$criteria = Criteria::create(TestLazy::dao());
			
			$this->assertEquals(
				array(TestLazy::dao()->getTable()),
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}
		
		public function testTableLazyListById()
		{
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::eq('id', DBValue::create('33')));
			
			$expectTags = array(
				TestLazy::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'33',
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}
		
		public function testTableLazyListByCities()
		{
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::eq('city', DBValue::create('1')))
				->add(Expression::eq(DBValue::create('2'), 'cityOptional.id'));
			
			$expectTags = array(
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'1',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'2',
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}
		
		public function testTableLazyListByCityName()
		{	
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::eq('city.name', 'Moscow'));
			
			$expectTags = array(
				TestLazy::dao()->getTable(),
				TestCity::dao()->getTable(),
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}
		
		public function testTableUserListFetchedWithJoins()
		{
			$criteria = Criteria::create(TestUser::dao());
			
			$expectTags = array(
				TestUser::dao()->getTable(),
				TestCity::dao()->getTable(),
				TestCity::dao()->getTable(),
				TestCity::dao()->getTable(),
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestUser')
			);
		}

		public function testTableLazyThroughValueObject()
		{
			$criteria = Criteria::create(TestUserWithContact::dao())
				->add(Expression::eq('contacts.city.id', DBValue::create('3')));
			
			$expectTags = array(
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'3',
			);
			
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestUserWithContact')
			);
		}

		public function testTableLazyListInCities()
		{
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::in('city', array('1', '2', '42')));
			
			$expectTags = array(
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'1',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'2',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'42',
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}

		public function testTableLazyListInCitiesWithId()
		{
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::in('city.id', array('1', '2', '42')));

			$expectTags = array(
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'1',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'2',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'42',
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestLazy')
			);
		}

		/**
		 * @group tzz1
		 */
		public function testOneToManyBack()
		{
			$criteria = Criteria::create(TestUser::dao())
				->add(Expression::eq('parts.id', '1'));

			$expectTags = array(
				TestUser::dao()->getTable(),
				TestPart::dao()->getTable(),
				TestCity::dao()->getTable(),
				TestCity::dao()->getTable(),
				TestCity::dao()->getTable(),
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), '\Onphp\Test\TestUser')
			);
		}
		
		/**
		 * @return TaggableSmartHandler 
		 */
		private function spawnHandler()
		{
			return new TaggableSmartHandler();
		}
	}
?>