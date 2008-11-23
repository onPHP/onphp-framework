<?php
	/* $Id$ */
	
	final class FormPrimitivesStringTest extends TestCase
	{
		const MAX = 10;
		
		public function testClean()
		{
			$form = $this->makeForm();
			
			$this->formAssertsClean($form, 'property');
		}
		
		public function testValid()
		{
			$form = $this->makeForm();
			
			$raw = 'ooOOoOo';
			
			$form->import(
				array('property' => $raw)
			);
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', $raw, $raw, $raw, $raw);
			$this->formErrorAsserts($form, false, false);
		}
		
		public function testInvalidFormat()
		{
			$form = $this->makeForm();
			
			$raw = '0';
			
			$form->import(
				array('property' => $raw)
			);
			
			// NOTE: '0' is valid string, not a missing value
			
			// value, raw, form, export
			$this->formAsserts($form, 'property', $raw, $raw, $raw, $raw);
			$this->formErrorAsserts($form, false, false);
		}
		
		protected function getPrimitive()
		{
			return Primitive::string(null)->setMax(self::MAX);
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