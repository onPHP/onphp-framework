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
	}
?>