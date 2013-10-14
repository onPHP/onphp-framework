<?php
	/* $Id$ */
	
	final class PrimitiveTimestampTest extends TestCase
	{
		public function testMarried()
		{
			$prm = Primitive::timestamp('test')->setComplex();
			
			$array = array(
				'test' => array(
					PrimitiveDate::DAY		=> '1',
					PrimitiveDate::MONTH	=> '2',
					PrimitiveDate::YEAR		=> '',
					PrimitiveTimestamp::HOURS	=> '17',
					PrimitiveTimestamp::MINUTES	=> '38',
					PrimitiveTimestamp::SECONDS	=> '59'
				)
			);
			
			$this->assertFalse($prm->import($array));
			$this->assertEquals($prm->getRawValue(), $array['test']);

			$this->assertEmpty(
				array_filter($prm->exportValue())
			);

			$array['test'][PrimitiveDate::YEAR] = '3456';
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue($prm->getValue()->getYear() == 3456);

			$this->assertEquals(
				$array['test'],
				$prm->exportValue()
			);
			
			$array['test'][PrimitiveDate::YEAR] = '2006';
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateTime(),
				'2006-02-01 17:38.59'
			);
			
			$this->assertFalse($prm->importSingle($array)); // not single
		}
		
		public function testMarriedOptional()
		{
			$prm =
				Primitive::timestamp('test')->
				setComplex()->
				optional();
			
			$array = array(
				'test' => array(
					PrimitiveDate::DAY		=> '',
					PrimitiveDate::MONTH	=> '',
					PrimitiveDate::YEAR		=> '',
					PrimitiveTimestamp::HOURS	=> '',
					PrimitiveTimestamp::MINUTES	=> '',
					PrimitiveTimestamp::SECONDS	=> ''
				)
			);
			
			$this->assertTrue($prm->import($array));
		}
		
		public function testSingle()
		{
			$prm = Primitive::timestamp('test')->setSingle();
			
			$array = array('test' => '1234-01-02 17:38:59');
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue($prm->getValue()->getYear() == 1234);
			
			$array = array('test' => '1975-01-02 17:38:59');
			
			$this->assertTrue($prm->import($array));
			
			$this->assertEquals(
				$prm->getValue()->toDateTime(),
				'1975-01-02 17:38.59'
			);
		}
	}
?>