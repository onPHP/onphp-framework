<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveEnumerationTest extends TestCase
	{
		public function testIntegerValues()
		{
			$form =
				\Onphp\Form::create()->
				add(
					\Onphp\Primitive::enumeration('enum')->of('\Onphp\DataType')
				);
			
			$form->import(array('enum' => '4097'));
			
			$this->assertEquals($form->getValue('enum')->getId(), 0x001001);
			$this->assertSame($form->getValue('enum')->getId(), 0x001001);
		}
		
		public function testGetList()
		{
			$primitive = \Onphp\Primitive::enumeration('enum')->of('\Onphp\DataType');
			$enum = \Onphp\DataType::create(\Onphp\DataType::getAnyId());
			
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->setDefault($enum);
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->import(array('enum' => \Onphp\DataType::getAnyId()));
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
		}
	}
?>