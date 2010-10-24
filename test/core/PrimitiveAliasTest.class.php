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

			$enumPrimitive =
				Primitive::enumeration('enumerationPrimitive')->
				of('DataType')->
				required();

			$form =
				Form::create()->
				add($enumPrimitive)->
				add(Primitive::alias('alias', $enumPrimitive))->
				import(array('alias' => DataType::getAnyId()));

			$errors = $form->getErrors();

			$this->assertFalse(isset($errors['enumerationPrimitive']));
		}
	}
?>