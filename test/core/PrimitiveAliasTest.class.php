<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveCustomError extends \Onphp\PrimitiveString
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
				\Onphp\Primitive::string('stringPrimitive')->
				required();
			
			$form =
				\Onphp\Form::create()->
				add($realPrimitive)->
				add(\Onphp\Primitive::alias('alias',$realPrimitive))->
				import(array('alias' => 'Йа строка'));
			
			$errors = $form->getErrors();
			
			$this->assertFalse(isset($errors['stringPrimitive']));

			$enumPrimitive =
				\Onphp\Primitive::enumeration('enumerationPrimitive')->
				of('\Onphp\DataType')->
				required();

			$form =
				\Onphp\Form::create()->
				add($enumPrimitive)->
				add(\Onphp\Primitive::alias('alias', $enumPrimitive))->
				import(array('alias' => \Onphp\DataType::getAnyId()));

			$errors = $form->getErrors();

			$this->assertFalse(isset($errors['enumerationPrimitive']));
		}
		
		public function testCustomError()
		{
			$realPrimitive = new PrimitiveCustomError('customError');
			$realPrimitive->setMax(1);
			
			$form =
				\Onphp\Form::create()->
				add($realPrimitive)->
				add(\Onphp\Primitive::alias('alias', $realPrimitive))->
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