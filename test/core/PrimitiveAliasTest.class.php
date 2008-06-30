<?php
	/* $Id$ */
	
	final class PrimitiveAliasTest extends TestCase
	{
		public function testImport()
		{
			$realPrimitive =
				Primitive::string('stringPrimitive')->
				required();
			
			$form =
				Form::create()->
				add($realPrimitive)->
				add(Primitive::alias('alias',$realPrimitive))->
				import(array('alias' => 'Йа строка'));
			
			$errors = $form->getErrors();
			
			$this->assertFalse(isset($errors['stringPrimitive']));
		}
	}
?>