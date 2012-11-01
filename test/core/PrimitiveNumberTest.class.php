<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveNumberTest extends TestCase
	{
		public function testInteger()
		{
			$prm = \Onphp\Primitive::integer('int');
			
			$this->assertTrue($prm->importValue(0));
			
			$this->assertFalse($prm->importValue('abc'));
		}
	}
?>