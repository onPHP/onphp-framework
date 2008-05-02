<?php
	/* $Id$ */
	
	final class FormTest extends TestCase
	{
		public function testRange()
		{
			$scope = array(
				'test' => array(
					PrimitiveRange::MIN => '42',
					PrimitiveRange::MAX => '64',
				)
			);
			
			$form =
				Form::create()->add(
					Primitive::range('test')
				)->
				import($scope);
			
			$this->assertEquals(
				$form->get('test')->getValue()->getMin(),
				$form->get('test')->getMin()
			);
			
			$this->assertEquals(
				$form->get('test')->getValue()->getMax(),
				$form->get('test')->getMax()
			);
			
			$this->assertEquals(
				$form->get('test')->getValue()->getStart(),
				$form->get('test')->getStart()
			);
			
			$this->assertEquals(
				$form->get('test')->getValue()->getEnd(),
				$form->get('test')->getEnd()
			);
			
			$this->assertEquals(
				$form->get('test')->getStart(), 42
			);
			
			$this->assertEquals(
				$form->get('test')->getEnd(), 64
			);
		}
		
		public function testSafeValues()
		{
			$prm = Primitive::date('date');
			$date = Date::create('2005-02-19');
			
			$prm->import(
				array('date' => '2005-02-19')
			);
			
			$this->assertTrue($prm->isImported());
			
			$this->assertEquals(
				$prm->getSafeValue(), $date
			);
			
			$prm = Primitive::date('date')->setDefault(
				$date
			);
			
			$prm->import(
				array('date' => 'omgEvilInput')
			);
			
			$this->assertTrue($prm->isImported());
			
			$this->assertTrue(
				$prm->getSafeValue() === null
			);
		}
	}
?>