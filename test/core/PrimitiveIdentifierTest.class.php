<?php
	/* $Id$ */
	
	final class PrimitiveIdentifierTest extends TestCaseDAO
	{
		public function testEmpty()
		{
			$prm = Primitive::identifier('name')->of('TestCity');
			
			$nullValues = array(null, '');
			foreach ($nullValues as $value) {
				$this->assertNull($prm->import(array('name' => $value)));
				$this->assertNull($prm->importValue($value));
			}
			
			$emptyValues = array(0, '0', false);
			
			foreach ($emptyValues as $value) {
				$this->assertFalse($prm->import(array('name' => $value)));
				$this->assertFalse($prm->importValue($value));
			}
		}
		
		/**
		 * @group pi
		 */
		public function testCustomImportExport()
		{
			$dbs = DBTestPool::me()->getPool();
			if (empty($dbs)) {
				$this->fail('For test required at least one DB in config');
			}
			DBPool::me()->setDefault(reset($dbs));
			
			$moscow = TestCity::create()->setCapital(true)->setName('Moscow');
			$moscow->dao()->add($moscow);

			$stalingrad = TestCity::create()->setCapital(false)->setName('Stalingrad');
			$stalingrad->dao()->add($stalingrad);

			$prms = array();
			$prms[] = Primitive::identifier('city')->
				setScalar(true)->
				of('TestCity')->
				setMethodName('PrimitiveIdentifierTest::getCityByName')->
				setExtractMethod('PrimitiveIdentifierTest::getCityName');
			
			$prms[] = Primitive::identifier('city')->
				setScalar(true)->
				of('TestCity')->
				setMethodName(array(get_class($this), 'getCityByName'))->
				setExtractMethod(function(TestCity $city) {return $city->getName();});
			
			foreach ($prms as $prm) {
				$prm->import(array('city' => 'Moscow'));
				$this->assertEquals($moscow, $prm->getValue());
				$this->assertEquals('Moscow', $prm->exportValue());

				$prm->importValue($stalingrad);
				$this->assertequals($stalingrad, $prm->getValue());
				$this->assertequals('Stalingrad', $prm->exportValue());

				$prm->import(array('city' => $moscow));
				$this->assertEquals($moscow, $prm->getValue());
				$this->assertEquals('Moscow', $prm->exportValue());
			}
		}
		
		/**
		 * @param string $name
		 * @return TestCity
		 */
		public static function getCityByName($name)
		{
			return Criteria::create(TestCity::dao())->
				add(Expression::eq('name', DBValue::create($name)))->
				get();
		}
		
		public static function getCityName(TestCity $city)
		{
			return $city->getName();
		}
	}
?>