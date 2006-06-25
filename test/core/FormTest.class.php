<?php
	/* $Id$ */
	
	final class FormTest extends UnitTestCase
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
			
			$this->assertTrue(
				$form->get('test')->getValue()->getMin()
				=== $form->get('test')->getMin()
			);
			
			$this->assertTrue(
				$form->get('test')->getValue()->getMax()
				=== $form->get('test')->getMax()
			);
			
			$this->assertTrue(
				$form->get('test')->getMin() == 42
			);
			
			$this->assertTrue(
				$form->get('test')->getMax() == 64
			);
		}
	}
?>