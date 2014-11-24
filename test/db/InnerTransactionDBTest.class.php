<?php
	namespace Onphp\Test;

	class InnerTransactionDBTest extends TestCaseDAO
	{
		public function testSavepoint()
		{
			foreach (DBTestPool::me()->iterator() as $connector => $db) {
				$this->getDBCreator()->fillDB();

				/* @var $moscow TestCity */
				$moscow = TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'Moscow'));

				$savePointName = 'svp';
				$db->begin();
				$db->savepointBegin($savePointName);

				$moscow->dao()->save($moscow->setName($newName = 'New Moscow'));

				$db->savepointRelease($savePointName);
				$db->commit();

				$this->assertNotNull(TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', $newName)));
			}
		}

		public function testSavepointRollback()
		{
			foreach (DBTestPool::me()->iterator() as $connector => $db) {
				$this->getDBCreator()->fillDB();

				/* @var $moscow TestCity */
				$moscow = TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'Moscow'));

				$savePointName = 'svp';
				$db->begin();
				$db->savepointBegin($savePointName);

				$moscow->dao()->save($moscow->setName($newName = 'New Moscow'));

				$db->savepointRollback($savePointName);
				$db->commit();

				try {
					TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', $newName));
					$this->fail('expects object not found exception');
				} catch (\Onphp\ObjectNotFoundException $e) {
					/* ok */
				}
			}
		}

		public function testInnerTransaction()
		{
			foreach (DBTestPool::me()->iterator() as $db) {
				$this->getDBCreator()->fillDB();
				
				$moscow = TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'Moscow'));
				$piter = TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'Saint-Peterburg'));
				
				$cityNewer = function(TestCity $city) {
					$city->dao()->merge($city->setName('New '.$city->getName()));
				};
				
				$citiesNewer = function($moscow, $piter) use ($cityNewer, $db) {
					$cityNewer($moscow);
					
					\Onphp\InnerTransactionWrapper::create()->
						setDB($db)->
						setFunction($cityNewer)->
						run($piter);
				};
				
				\Onphp\InnerTransactionWrapper::create()->
					setDao($moscow->dao())->
					setFunction($citiesNewer)->
					run($moscow, $piter);
				
				$this->assertNotNull(TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'New Moscow')));
				$this->assertNotNull(TestCity::dao()->getByLogic(\Onphp\Expression::eq('name', 'New Saint-Peterburg')));
			}
		}
	}
?>