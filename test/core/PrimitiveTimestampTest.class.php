<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveTimestampTest extends TestCase
	{
		public function testMarried()
		{
			$prm = \Onphp\Primitive::timestamp('test')->setComplex();
			
			$array = array(
				'test' => array(
					\Onphp\PrimitiveDate::DAY		=> '1',
					\Onphp\PrimitiveDate::MONTH	=> '2',
					\Onphp\PrimitiveDate::YEAR		=> '',
					\Onphp\PrimitiveTimestamp::HOURS	=> '17',
					\Onphp\PrimitiveTimestamp::MINUTES	=> '38',
					\Onphp\PrimitiveTimestamp::SECONDS	=> '59'
				)
			);
			
			$this->assertFalse($prm->import($array));
			$this->assertEquals($prm->getRawValue(), $array['test']);

			$this->assertEmpty(
				array_filter($prm->exportValue())
			);
			
			$array['test'][\Onphp\PrimitiveDate::YEAR] = '3456';
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue($prm->getValue()->getYear() == 3456);

			$this->assertEquals(
				$array['test'],
				$prm->exportValue()
			);
			
			$array['test'][\Onphp\PrimitiveDate::YEAR] = '2006';
			
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
				\Onphp\Primitive::timestamp('test')->
				setComplex()->
				optional();
			
			$array = array(
				'test' => array(
					\Onphp\PrimitiveDate::DAY		=> '',
					\Onphp\PrimitiveDate::MONTH	=> '',
					\Onphp\PrimitiveDate::YEAR		=> '',
					\Onphp\PrimitiveTimestamp::HOURS	=> '',
					\Onphp\PrimitiveTimestamp::MINUTES	=> '',
					\Onphp\PrimitiveTimestamp::SECONDS	=> ''
				)
			);
			
			$this->assertTrue($prm->import($array));
		}
		
		public function testSingle()
		{
			$prm = \Onphp\Primitive::timestamp('test')->setSingle();
			
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