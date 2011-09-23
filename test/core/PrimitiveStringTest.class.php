<?php

	final class PrimitiveStringTest extends TestCase
	{
		public function testImport()
		{
			$prm = Primitive::string('name');
			
			$nullValues = array(null, '');
			
			foreach ($nullValues as $value)
				$this->assertNull($prm->importValue($value));
			
			$falseValues = array(array(), true, false, $prm);
			
			foreach ($falseValues as $value)
				$this->assertFalse($prm->importValue($value));
			
			$trueValues = array('some string', -100500, 2011.09);
			
			foreach ($trueValues as $value)
				$this->assertTrue($prm->importValue($value));
		}
	}
?>