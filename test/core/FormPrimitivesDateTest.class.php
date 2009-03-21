<?php
	/* $Id$ */
	
	final class FormPrimitivesDateTest extends TestCase
	{
		public function testValidScope()
		{
			$data =
				array(
					PrimitiveDate::DAY => '22',
					PrimitiveDate::MONTH => '03',
					PrimitiveDate::YEAR => '2009',
				);
			
			$scope = array(
				'test' => $data
			);
			
			$form =
				Form::create()->add(
					Primitive::date('test')
				)->
				import($scope);
			
			$this->assertEquals(
				$form->getValue('test')->getDay(),
				22
			);
			
			$this->assertEquals(
				$form->getValue('test')->getMonth(),
				3
			);
			
			$this->assertEquals(
				$form->getValue('test')->getYear(),
				2009
			);
			
			$this->assertEquals(
				$form->getRawValue('test'),
				$data
			);
			
			$this->assertEquals(
				$form->get('test')->isImported(),
				true
			);
			
			$this->assertEquals(
				$form->getErrors(),
				array()
			);
		}
		
		public function testInvalidScope()
		{
			$data =
				array(
					PrimitiveDate::DAY => '22',
					PrimitiveDate::MONTH => '14',
					PrimitiveDate::YEAR => '2009',
				);
			
			$scope = array(
				'test' => $data
			);
			
			$form =
				Form::create()->add(
					Primitive::date('test')
				)->
				import($scope);
			
			$this->assertEquals(
				$form->getValue('test'),
				null
			);
			
			$this->assertEquals(
				$form->getRawValue('test'),
				$data
			);
			
			$this->assertEquals(
				$form->get('test')->isImported(),
				true
			);
			
			$this->assertEquals(
				$form->getErrors(),
				array(
					'test' => Form::WRONG,
				)
			);
		}
		
		public function testEmptyScope()
		{
			$data =
				array(
					PrimitiveDate::DAY => '',
					PrimitiveDate::MONTH => '',
					PrimitiveDate::YEAR => '',
				);
			
			$scope = array(
				'test' => $data
			);
			
			$form =
				Form::create()->add(
					Primitive::date('test')
				)->
				import($scope);
			
			$this->assertEquals(
				$form->getValue('test'),
				null
			);
			
			$this->assertEquals(
				$form->get('test')->isImported(),
				true
			);
			
			$this->assertEquals(
				$form->getErrors(),
				array()
			);
			
			$this->assertEquals(
				$form->getRawValue('test'),
				$data
			);
		}
		
		public function testEmptyScopeWithRequired()
		{
			$data =
				array(
					PrimitiveDate::DAY => '',
					PrimitiveDate::MONTH => '',
					PrimitiveDate::YEAR => '',
				);
			
			$scope = array(
				'test' => $data
			);
			
			$form =
				Form::create()->add(
					Primitive::date('test')->
					required()
				)->
				import($scope);
			
			$this->assertEquals(
				$form->getValue('test'),
				null
			);
			
			$this->assertEquals(
				$form->get('test')->isImported(),
				true
			);
			
			$this->assertEquals(
				$form->getRawValue('test'),
				$data
			);
			
			$this->assertEquals(
				$form->getErrors(),
				array(
					'test' => Form::MISSING,
				)
			);
		}
	}
?>