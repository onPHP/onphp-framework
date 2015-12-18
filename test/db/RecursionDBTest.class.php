<?php
	/**
	 * @group rdb
	 */
	class RecursionDBTest extends TestCaseDAO
	{
		/**
		 * @see http://lists.shadanakar.org/onphp-dev-ru/0811/0774.html
		**/
		public function testRecursiveContainers()
		{
			$this->markTestSkipped('wontfix');
			
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);
				TestObject::dao()->import(
					TestObject::create()->
					setId(1)->
					setName('test object')
				);

				TestType::dao()->import(
					TestType::create()->
					setId(1)->
					setName('test type')
				);

				$type = TestType::dao()->getById(1);

				$type->getObjects()->fetch()->setList(
					array(TestObject::dao()->getById(1))
				)->
				save();

				$object = TestObject::dao()->getById(1);

				TestObject::dao()->save($object->setName('test object modified'));

				$list = $type->getObjects()->getList();

				$modifiedObject = TestObject::dao()->getById(1);

				$this->assertEquals($list[0], $modifiedObject);
			}
		}
		
		public function testRecursionObjects()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);
				
				$parentProperties =
					Singleton::getInstance('ProtoTestParentObject')->
					getPropertyList();

				$resultRoot = $parentProperties['root']->
					getFetchStrategyId() == FetchStrategy::LAZY;

				$childProperties =
					Singleton::getInstance('ProtoTestChildObject')->
					getPropertyList();

				$resultParent = $childProperties['parent']->
					getFetchStrategyId() == FetchStrategy::LAZY;

				$selfRecursiveProperties =
					Singleton::getInstance('ProtoTestSelfRecursion')->
					getPropertyList();

				$resultSelfRecursive = $selfRecursiveProperties['parent']->
					getFetchStrategyId() == FetchStrategy::LAZY;

				$this->assertTrue($resultRoot);
				$this->assertTrue($resultParent);
				$this->assertTrue($resultSelfRecursive);
			}
		}
	}
?>