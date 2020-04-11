<?php
	/* $Id$ */
	
	final class PrimitiveNumberTest extends TestCase
	{
		public function testInteger()
		{
			$prm = Primitive::integer('int');
			
			$this->assertTrue($prm->importValue(0));
			
			$this->assertFalse($prm->importValue('abc'));
		}
	}
?>