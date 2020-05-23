<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Logic\Expression;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Criteria\Projection;
use OnPHP\Tests\Meta\Business\TestChildObject;
use OnPHP\Tests\Meta\Business\TestEncapsulant;
use OnPHP\Tests\Meta\Business\TestItem;
use OnPHP\Tests\Meta\Business\TestParentObject;
use OnPHP\Tests\Meta\Business\TestSubItem;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

/**
 * @group core
 * @group db
 * @group dao
 * @group criteria
 */
class CacheAndLazyDBTest extends TestCaseDAO
{
	public function testWorkingWithCache()
	{
		foreach (DBTestPool::me()->getPool() as $db) {
			DBPool::me()->setDefault($db);
			
			$item =
				TestItem::create()->
				setName('testItem1');
			
			TestItem::dao()->add($item);
			
			$encapsulant =
				TestEncapsulant::create()->
				setName('testEncapsulant1');
			
			TestEncapsulant::dao()->add($encapsulant);
			
			$subItem1 =
				TestSubItem::create()->
				setName('testSubItem1')->
				setEncapsulant($encapsulant)->
				setItem($item);
			
			$subItem2 =
				TestSubItem::create()->
				setName('testSubItem2')->
				setEncapsulant($encapsulant)->
				setItem($item);
			
			TestSubItem::dao()->add($subItem1);
			TestSubItem::dao()->add($subItem2);
			
			$items =
				Criteria::create(TestItem::dao())->
				getList();
			
			foreach ($items as $item) {
				foreach ($item->getSubItems()->getList() as $subItem) {
					$this->assertEquals(
						$subItem->getEncapsulant()->getName(),
						'testEncapsulant1'
					);
				}
			}
			
			$encapsulant = TestEncapsulant::dao()->getById(1);
			
			$encapsulant->setName('testEncapsulant1_changed');
			
			TestEncapsulant::dao()->save($encapsulant);
			
			// drop identityMap
			TestEncapsulant::dao()->dropIdentityMap();
			TestSubItem::dao()->dropIdentityMap();
			TestItem::dao()->dropIdentityMap();
			
			$items =
				Criteria::create(TestItem::dao())->
				getList();
			
			foreach ($items as $item) {
				foreach ($item->getSubItems()->getList() as $subItem) {
					$this->assertEquals(
						$subItem->getEncapsulant()->getName(),
						'testEncapsulant1_changed'
					);
				}
			}
			
			// drop identityMap
			TestEncapsulant::dao()->dropIdentityMap();
			TestSubItem::dao()->dropIdentityMap();
			TestItem::dao()->dropIdentityMap();
			
			$subItem = TestSubItem::dao()->getById(1);
			
			$this->assertEquals(
				$subItem->getEncapsulant()->getName(),
				'testEncapsulant1_changed'
			);
			
			// drop identityMap
			TestEncapsulant::dao()->dropIdentityMap();
			TestSubItem::dao()->dropIdentityMap();
			TestItem::dao()->dropIdentityMap();
			
			$subItems =
				Criteria::create(TestSubItem::dao())->
				getList();
			
			foreach ($subItems as $subItem) {
				$this->assertEquals(
					$subItem->getEncapsulant()->getName(),
					'testEncapsulant1_changed'
				);
			}
		}
	}
	
	public function testLazy()
	{
		foreach (DBTestPool::me()->getPool() as $db) {
			DBPool::me()->setDefault($db);
			
			$parent = TestParentObject::create();
			$child = TestChildObject::create()->setParent($parent);

			$parent->dao()->add($parent);

			$child->dao()->add($child);

			$this->assertEquals(
				$parent->getId(),
				Criteria::create(TestChildObject::dao())->
					setProjection(
						Projection::property('parent.id', 'parentId')
					)->
					add(Expression::eq('id', $child->getId()))->
					getCustom('parentId')
			);
		}
	}
}
?>