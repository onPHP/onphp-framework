<?php
	/**
	 * @group rdb
	 */
	namespace Onphp\Test;

	class RecursionDBTest extends TestCaseDAO
	{
		/**
		 * @see http://lists.shadanakar.org/onphp-dev-ru/0811/0774.html
		**/
		public function testRecursiveContainers()
		{
			$this->markTestSkipped('wontfix');
			
			foreach (DBTestPool::me()->getPool() as $db) {
				\Onphp\DBPool::me()->setDefault($db);
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
				\Onphp\DBPool::me()->setDefault($db);
				
				$parentProperties =
					\Onphp\Singleton::getInstance('\Onphp\Test\ProtoTestParentObject')->
					getPropertyList();

				$resultRoot = $parentProperties['root']->
					getFetchStrategyId() == \Onphp\FetchStrategy::LAZY;

				$childProperties =
					\Onphp\Singleton::getInstance('\Onphp\Test\ProtoTestChildObject')->
					getPropertyList();

				$resultParent = $childProperties['parent']->
					getFetchStrategyId() == \Onphp\FetchStrategy::LAZY;

				$selfRecursiveProperties =
					\Onphp\Singleton::getInstance('\Onphp\Test\ProtoTestSelfRecursion')->
					getPropertyList();

				$resultSelfRecursive = $selfRecursiveProperties['parent']->
					getFetchStrategyId() == \Onphp\FetchStrategy::LAZY;

				$this->assertTrue($resultRoot);
				$this->assertTrue($resultParent);
				$this->assertTrue($resultSelfRecursive);
			}
		}
	}
?>