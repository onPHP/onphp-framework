<?php
	namespace Onphp\Test;

	class InnerTransactionDBTest extends TestCaseDAO
	{
		public function testInnerTransaction()
		{
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
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