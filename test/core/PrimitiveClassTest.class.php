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
		
		public function testOf()
		{
			$prm = Primitive::clazz('name');
			
			try {
				$prm->of('InExIsNaNtClass');
				$this->fail();
			} catch (WrongArgumentException $e) {
				// pass
			}
			
			$this->assertFalse(
				$prm->
					of('Enumeration')->
					importValue('IdentifiableObject')
			);
			
			$this->assertTrue(
				$prm->
					of('Identifiable')->
					importValue('IdentifiableObject')
			);
			
			$this->assertTrue(
				$prm->
					of('IdentifiableObject')->
					importValue('IdentifiableObject')
			);
		}
	}
?>