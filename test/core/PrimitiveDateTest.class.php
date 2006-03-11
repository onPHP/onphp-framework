<?php
	/* $Id$ */
	
	final class PrimitiveDateTest extends UnitTestCase
	{
		public function testMarried()
		{
			$prm = Primitive::date('test')->setComplex();
			
			$array = array(
				'test' => array(
					PrimitiveDate::DAY		=> '1',
					PrimitiveDate::MONTH	=> '2',
					PrimitiveDate::YEAR		=> '3456',
					PrimitiveDate::HOURS	=> '17',
					PrimitiveDate::MINUTES	=> '38',
					PrimitiveDate::SECONDS	=> '59'
				)
			);
			
			$this->assertFalse($prm->import($array)); // wrong year
			
			$array['test'][PrimitiveDate::YEAR] = '2006';
			
			$this->assertTrue($prm->import($array));
			$this->assertEqual(
				$prm->getValue()->toDateTime(),
				'2006-02-01 17:38.59'
			);
			
			$this->assertFalse($prm->importSingle($array)); // not single
		}
		
		public function testSingle()
		{
			$prm = Primitive::date('test')->setSingle();
			
			$array = array('test' => '1234-1-2 17:38:59');
			
			$this->assertFalse($prm->import($array)); // wrong year
			
			$array = array('test' => '1975-1-2 17:38:59');
			
			$this->assertTrue($prm->import($array));
			
			$this->assertEqual(
				$prm->getValue()->toDateTime(),
				'1975-01-02 17:38.59'
			);
		}
	}
?>