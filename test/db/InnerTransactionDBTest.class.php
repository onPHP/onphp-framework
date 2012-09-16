<?php
	class InnerTransactionDBTest extends TestCaseDAO
	{
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