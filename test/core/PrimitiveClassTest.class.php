<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveClassTest extends TestCase
	{
		public function test()
		{
			$prm = \Onphp\Primitive::clazz('name');
			
			$this->assertFalse($prm->import(array('name' =>'InExIsTaNtClass')));
			$this->assertFalse($prm->import(array('name' => "\0foo")));
			
			$this->assertTrue($prm->importValue('\Onphp\IdentifiableObject'));
			$this->assertEquals($prm->getValue(), '\Onphp\IdentifiableObject');
		}
		
		public function testOf()
		{
			$prm = \Onphp\Primitive::clazz('name');
			
			try {
				$prm->of('InExIsNaNtClass');
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				// pass
			}
			
			$this->assertFalse(
				$prm->
					of('\Onphp\Enumeration')->
					importValue('\Onphp\IdentifiableObject')
			);
			
			$this->assertTrue(
				$prm->
					of('\Onphp\Identifiable')->
					importValue('\Onphp\IdentifiableObject')
			);
			
			$this->assertTrue(
				$prm->
					of('\Onphp\IdentifiableObject')->
					importValue('\Onphp\IdentifiableObject')
			);
		}
	}
?>