<?php
	/* $Id$ */
	
	final class PrimitiveIdentifierTest extends TestCase
	{
		public function testEmpty()
		{
			$prm = Primitive::prototypedIdentifier('TestCity', 'name');
			
			$nullValues = array(null, '', false);
			foreach ($nullValues as $value) {
				$this->assertNull($prm->import(array('name' => $value)));
				$this->assertNull($prm->importValue($value));
			}
			
			$emptyValues = array(0, '0');
			
			foreach ($emptyValues as $value) {
				$this->assertFalse($prm->import(array('name' => $value)));
				$this->assertFalse($prm->importValue($value));
			}
		}
	}
?>