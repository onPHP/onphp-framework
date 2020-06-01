<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\DB\MySQLim;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\DB\SQLitePDO;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\Exception\DatabaseException;
use OnPHP\Core\Exception\DuplicateObjectException;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Criteria\Projection;
use OnPHP\Main\Util\ArrayUtils;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\Meta\Business\TestEncapsulant;
use OnPHP\Tests\Meta\Business\TestItem;
use OnPHP\Tests\Meta\Business\TestSubItem;

/**
 * @group core
 * @group db
 * @group dao
 * @group mysql
 * @group sqlite
 * @group postgresql
 */
class DuplicateObjectExceptionTest extends TestCaseDAO
{	
	/**
	 * @dataProvider dbConnections
	 */
	public function testInsert($db)
	{
		DBPool::me()->setDefault($db);
		
		DBPool::me()->getLink()->queryRaw("CREATE UNIQUE INDEX uq_name ON custom_table (name)");
		
		$moscow	    = TestCity::create()->setName('Moscow');
		$perm	    = TestCity::create()->setName('Moscow');
		$domodedovo = TestCity::create()->setName('Moscow');
		$kaluga	    = TestCity::create()->setName('Kaluga');

		
		$moscow = $moscow->dao()->add($moscow);

		try {
		    $perm = $perm->dao()->add($perm);
		} catch (DatabaseException $e) { /** **/ }

		try {
		    $domodedovo = $domodedovo->dao()->add($domodedovo);
		} catch (DatabaseException $e) { /** **/ }

		$kaluga = $kaluga->dao()->add($kaluga);

		$domodedovo = $domodedovo->dao()->add($domodedovo->setName('Domodedovo'));
		$perm = $perm->dao()->add($perm->setName('Perm'));

		$this->assertIsNumeric($moscow->getId());
		$this->assertIsNumeric($perm->getId());
		$this->assertIsNumeric($domodedovo->getId());
		$this->assertIsNumeric($kaluga->getId());

		$moscowFromDb	    = TestCity::dao()->getByLogic(Expression::eq('name', 'Moscow'));
		$permFromDb	    = TestCity::dao()->getByLogic(Expression::eq('name', 'Perm'));
		$domodedovoFromDb   = TestCity::dao()->getByLogic(Expression::eq('name', 'Domodedovo'));
		$kalugaFromDb	    = TestCity::dao()->getByLogic(Expression::eq('name', 'Kaluga'));

		$this->assertEquals($moscow->getId(), $moscowFromDb->getId());
		$this->assertEquals($perm->getId(), $permFromDb->getId());
		$this->assertEquals($domodedovo->getId(), $domodedovoFromDb->getId());
		$this->assertEquals($kaluga->getId(), $kalugaFromDb->getId());
	}
	
	/**
	 * @dataProvider dbConnections
	 */
	public function testInsertOneToMany($db)
	{
		DBPool::me()->setDefault($db);
		DBPool::me()->getLink()->queryRaw("CREATE UNIQUE INDEX uq_name ON test_sub_item (name)");

		$encapsulant = TestEncapsulant::create()->setName('Encapsulant');
		$encapsulant = $encapsulant->dao()->add($encapsulant);

		$testItem = TestItem::dao()->add(TestItem::create()->setName('TestItem'));

		$listDuplicate = array(
			TestSubItem::create()->setEncapsulant($encapsulant)->setName('One')->setItem($testItem),
			TestSubItem::create()->setEncapsulant($encapsulant)->setName('Two')->setItem($testItem),
			TestSubItem::create()->setEncapsulant($encapsulant)->setName('Three')->setItem($testItem),
			TestSubItem::create()->setEncapsulant($encapsulant)->setName('Three')->setItem($testItem)
		);

		try {
			$testItem->getSubItems()->fetch()->setList($listDuplicate)->save();
		} catch(\Exception $e) {
			/**
			 * Becase SQLite throw DatabaseException not DuplicateObjectException
			 */
			$this->assertTrue(is_a($e, 'OnPHP\Core\Exception\DatabaseException', true));
		}

		$list = $testItem->getSubItems()->getList();
		$list = array_slice($list, 0, 3);
		$list[] = TestSubItem::create()->setEncapsulant($encapsulant)->setName('Four')->setItem($testItem);
		$testItem->getSubItems()->setList($list)->save();

		/**
		 * Need array_values, because UnifiedContainer return key as value
		 */
		$idsUnifiedContainer = array_values($testItem->getSubItems(true)->fetch()->getList());
		$idsCriteria =
			ArrayUtils::convertToPlainList(
				Criteria::create(TestSubItem::dao())->
					addProjection(Projection::property('id'))->
					add(Expression::eq('item', $testItem->getId()))->
					getCustomList(),
				'id'
			);
		$this->assertEquals($idsUnifiedContainer, $idsCriteria);
		$this->assertEquals($idsUnifiedContainer, $idsCriteria);

		$list = $testItem->getSubItems()->fetch()->getList();
		/**
		 * Drop first elemet - wee need check drop from list
		 */
		$list = array_slice($list, 1);
		/**
		 * Add new element to list
		 */
		$list[] = TestSubItem::create()->setEncapsulant($encapsulant)->setName('Five')->setItem($testItem);
		/**
		 * Add new element with duplicate Name
		 */
		$list[] = TestSubItem::create()->setEncapsulant($encapsulant)->setName('Three')->setItem($testItem);

		try {
			$testItem->getSubItems()->setList($list)->save();
		} catch (\Exception $e) {
			/**
			 * Becase SQLite throw DatabaseException not DuplicateObjectException
			 */
			$this->assertTrue(is_a($e, 'OnPHP\Core\Exception\DatabaseException', true));
		}

		$list = $testItem->getSubItems()->getList();
		/**
		 * Delete first and last (duplicated) object from list
		 */
		$list = array_slice($list, 0, count($list) - 1);

		$testItem->getSubItems()->setList($list)->save();

		$idsUnifiedContainer = array_values($testItem->getSubItems(true)->fetch()->getList());
		$idsCriteria =
			ArrayUtils::convertToPlainList(
				Criteria::create(TestSubItem::dao())->
					addProjection(Projection::property('id'))->
					add(Expression::eq('item', $testItem->getId()))->
					getCustomList(),
				'id'
			);
		/**
		 * Check that one item was deleted and one added
		 */
		$this->assertEquals($idsUnifiedContainer, $idsCriteria);
	}

	public function dbConnections()
	{
		$result = array();
		foreach(DBTestPool::me()->getPool() as $db) {
		    $result[] = array($db);
		}
		return $result;
	}
}
?>