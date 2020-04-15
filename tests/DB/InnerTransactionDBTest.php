<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\DB\DB;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\DB\Transaction\InnerTransactionWrapper;
use OnPHP\Core\Exception\ObjectNotFoundException;
use OnPHP\Core\Logic\Expression;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

class InnerTransactionDBTest extends TestCaseDAO
{
	public function setUp() : void
	{
		if (empty(DBTestPool::me()->getPool())) {
			$this->fail('this test requires db in config');
		}
		parent::setUp();
	}

	public function testSavepoint()
	{
		foreach (DBTestPool::me()->getPool() as $connector => $db) {
			/* @var $db DB */
			DBPool::me()->setDefault($db);
			$this->getDBCreator()->fillDB();

			/* @var $moscow TestCity */
			$moscow = TestCity::dao()->getByLogic(Expression::eq('name', 'Moscow'));

			$savePointName = 'svp';
			$db->begin();
			$db->savepointBegin($savePointName);

			$moscow->dao()->save($moscow->setName($newName = 'New Moscow'));

			$db->savepointRelease($savePointName);
			$db->commit();

			$this->assertNotNull(TestCity::dao()->getByLogic(Expression::eq('name', $newName)));
		}
	}

	public function testSavepointRollback()
	{
		foreach (DBTestPool::me()->getPool() as $connector => $db) {
			/* @var $db DB */
			DBPool::me()->setDefault($db);
			$this->getDBCreator()->fillDB();

			/* @var $moscow TestCity */
			$moscow = TestCity::dao()->getByLogic(Expression::eq('name', 'Moscow'));

			$savePointName = 'svp';
			$db->begin();
			$db->savepointBegin($savePointName);

			$moscow->dao()->save($moscow->setName($newName = 'New Moscow'));

			$db->savepointRollback($savePointName);
			$db->commit();

			$this->expectException(ObjectNotFoundException::class);

			TestCity::dao()->getByLogic(Expression::eq('name', $newName));
		}
	}

	public function testInnerTransaction()
	{
		foreach (DBTestPool::me()->getPool() as $connector => $db) {
			DBPool::me()->setDefault($db);
			$this->getDBCreator()->fillDB();
			
			$moscow = TestCity::dao()->getByLogic(Expression::eq('name', 'Moscow'));
			$piter = TestCity::dao()->getByLogic(Expression::eq('name', 'Saint-Peterburg'));
			
			$cityNewer = function(TestCity $city) {
				$city->dao()->merge($city->setName('New '.$city->getName()));
			};
			
			$citiesNewer = function($moscow, $piter) use ($cityNewer, $db) {
				$cityNewer($moscow);
				
				InnerTransactionWrapper::create()->
					setDB($db)->
					setFunction($cityNewer)->
					run($piter);
			};
			
			InnerTransactionWrapper::create()->
				setDao($moscow->dao())->
				setFunction($citiesNewer)->
				run($moscow, $piter);
			
			$this->assertNotNull(TestCity::dao()->getByLogic(Expression::eq('name', 'New Moscow')));
			$this->assertNotNull(TestCity::dao()->getByLogic(Expression::eq('name', 'New Saint-Peterburg')));
		}
	}
}
?>