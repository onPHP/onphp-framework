<?php
	/* $Id$ */
	
	final class PrimitiveEnumerationTest extends TestCase
	{
		public function testIntegerValues()
		{
			$form = $this->getForm();
			
			$form->import(array('enum' => '4097'));
			
			$this->assertEquals($form->getValue('enum')->getId(), 0x001001);
			$this->assertSame($form->getValue('enum')->getId(), 0x001001);
		}
		
		public function testGetList()
		{
			$primitive = Primitive::enumeration('enum')->of('DataType');
			$enum = DataType::create(DataType::getAnyId());
			
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->setDefault($enum);
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
			
			$primitive->import(array('enum' => DataType::getAnyId()));
			$this->assertEquals($primitive->getList(), $enum->getObjectList());
		}
		
		public function testNonExsitingValue()
		{
			$form = $this->getForm();
						
			$form->get('enum')->
				setDefault(DataType::create(DataType::getAnyId()));
			
			$form->import(array('enum' => -10000));
			
			$this->assertFalse($form->get('enum')->isImported());
			$this->assertNull($form->getValue('enum'));
			$this->assertEquals(
				DataType::getAnyId(), 
				$form->getActualValue('enum')->getId()
			);
		}
		
		private function getForm()
		{
			return
				Form::create()->
					add(
						Primitive::enumeration('enum')->of('DataType')
					);
		}
	}
?>