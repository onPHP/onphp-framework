<?php
	/* $Id$ */
	
	final class PrimitiveEnumerationTest extends TestCase
	{
		public function testIntegerValues()
		{
			$form =
				Form::create()->
				add(
					Primitive::enumeration('enum')->of('DataType')
				);
			
			$form->import(array('enum' => '4097'));
			
			$this->assertEquals($form->getValue('enum')->getId(), 0x001001);
			$this->assertSame($form->getValue('enum')->getId(), 0x001001);
		}
		
		public function testGetList()
		{
			$primitive = Primitive::enumeration('enum')->of('DataType');
			$enum = DataType::create(DataType::getAnyId());
			
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->setValue($enum);
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->import(array('enum' => DataType::getAnyId()));
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
		}
	}
?>