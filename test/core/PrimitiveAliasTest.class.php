<?php
	/* $Id$ */
	
	final class PrimitiveCustomError extends PrimitiveString
	{
		const CUSTOM_MARK = 0xff;
		
		public function import($scope)
		{
			$this->customError = null;
			
			$result = parent::import($scope);
			
			if ($result === false)
				$this->customError = self::CUSTOM_MARK;
			
			return $result;
		}
	}
	
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
		
		public function testCustomError()
		{
			$realPrimitive = new PrimitiveCustomError('customError');
			$realPrimitive->setMax(1);
			
			$form =
				Form::create()->
				add($realPrimitive)->
				add(Primitive::alias('alias', $realPrimitive))->
				import(array('alias' => 'Toooo long'));
			
			$errors = $form->getErrors();
			
			$this->assertTrue(isset($errors['alias']));
			$this->assertEquals(PrimitiveCustomError::CUSTOM_MARK, $errors['alias']);
			
			$form->
				clean()->
				dropAllErrors()->
				import(array('customError' => 'Toooo long'));
			
			$errors = $form->getErrors();
			
			$this->assertTrue(isset($errors['customError']));
			$this->assertEquals(PrimitiveCustomError::CUSTOM_MARK, $errors['customError']);
		}
	}
?>