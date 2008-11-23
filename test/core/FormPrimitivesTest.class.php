<?php
	/* $Id$ */
	
	final class FormPrimitivesTest extends TestCase
	{
		public function testClean()
		{
			$form = $this->makeForm();
			
			$this->formAssertsClean($form, 'property');
		}
		
		public function testValid()
		{
			$form = $this->makeForm();
			
			$raw = 42;
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formErrorAsserts($form, false, false);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', 42, $raw, 42, 42);
		}
		
		public function testInvalidFormat()
		{
			$form = $this->makeForm();
			
			$raw = 'oOOooOoo';
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formAssertsWrong($form, 'property', $raw);
		}
		
		public function testInvalidConstraints()
		{
			$form = $this->makeForm();
			
			$raw = -1;
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formAssertsWrong($form, 'property', $raw);
		}
		
		public function testInvalidConstraintsWithDefault()
		{
			$form = $this->makeForm();
			
			$form->get('property')->
				setValue(42);
			
			$raw = -1;
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formErrorAsserts($form, true, true);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', 42, $raw, $raw, 42);
		}
		
		public function testBlank()
		{
			$form = $this->makeForm();
			
			$form->import(array());
			
			$this->formAssertsClean($form, 'property');
		}
		
		public function testNull()
		{
			$form = $this->makeForm();
			
			$raw = null;
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formAssertsClean($form, 'property');
		}
		
		public function testEmptyString()
		{
			$form = $this->makeForm();
			
			$raw = '';
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formAssertsClean($form, 'property');
		}
		
		public function testEmptyArray()
		{
			$form = $this->makeForm();
			
			$raw = array();
			
			$form->import(
				array('property' => $raw)
			);
			
			$this->formAssertsWrong($form, 'property', $raw);
		}
		
		protected function getPrimitive()
		{
			return Primitive::integer(null)->setMin(0);
		}
		
		protected function makeForm()
		{
			return
				Form::create()->
				add($this->getPrimitive()->spawn('property'));
		}
		
		
		protected function formAssertsClean(Form $form, $prm)
		{
			$this->formErrorAsserts($form, false, false);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', null, null, null, null);
		}
		
		protected function formAssertsMissing(Form $form, $prm, $default = null)
		{
			$this->formErrorAsserts($form, true, true);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', null, null, null, null);
		}
		
		protected function formAssertsWrong(Form $form, $prm, $raw, $default = null)
		{
			$this->formErrorAsserts($form, true, true);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', null, $raw, $raw, null);
		}
		
		private function formAsserts(
			Form $form,
			$prm,
			$getValue,
			$getRawValue,
			$getFormValue,
			$exportValue
		)
		{
			$this->assertEquals($getValue, $form->getValue($prm));
			$this->assertEquals($getRawValue, $form->getRawValue($prm));
			$this->assertEquals($getFormValue, $form->getFormValue($prm));
			$this->assertEquals($exportValue, $form->exportValue($prm));
		}
		
		private function formErrorAsserts(Form $form, $errors, $innerErrors)
		{
			if ($errors)
				$this->assertFalse(!$form->getErrors());
			else
				$this->assertTrue(!$form->getErrors());
			
			if ($innerErrors)
				$this->assertFalse(!$form->getInnerErrors());
			else
				$this->assertTrue(!$form->getInnerErrors());
		}
	}
?>