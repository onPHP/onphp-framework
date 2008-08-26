<?php
	/* $Id$ */
	
	final class PrimitiveClassTest extends TestCase
	{
		public function test()
		{
			$prm = Primitive::clazz('name');
			
			$this->assertFalse($prm->import(array('name' =>'InExIsTaNtClass')));
			$this->assertFalse($prm->import(array('name' => "\0foo")));
			
			$this->assertTrue($prm->importValue('IdentifiableObject'));
			$this->assertEquals($prm->getValue(), 'IdentifiableObject');
		}
	}
?>