<?php

	final class TaggableSmartHandlerTest extends TestCase
	{
		public function testTableLazyList()
		{
			$criteria = Criteria::create(TestLazy::dao());
			
			$this->assertEquals(
				array(TestLazy::dao()->getTable()),
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestLazy')
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
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestLazy')
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
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestLazy')
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
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestLazy')
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
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestUser')
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
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestUserWithContact')
			);
			
		}
		
		public function testTableLazyListInCities()
		{
			$this->markTestIncomplete('Not implemented feature, but you can ;)');
			
			$criteria = Criteria::create(TestLazy::dao())
				->add(Expression::in('city', array('1', '2', '42')));
			
			$expectTags = array(
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'1',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'2',
				TestCity::dao()->getTable().TaggableSmartHandler::ID_POSTFIX.'42',
			);
			$this->assertEquals(
				$expectTags,
				$this->spawnHandler()->getQueryTags($criteria->toSelectQuery(), 'TestLazy')
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