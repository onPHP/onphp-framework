<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveIdentifierTest extends TestCase
	{
		public function testEmpty()
		{
			$prm = \Onphp\Primitive::identifier('name')->of('\Onphp\Test\TestCity');
			
			$nullValues = array(null, '');
			foreach ($nullValues as $value) {
				$this->assertNull($prm->import(array('name' => $value)));
				$this->assertNull($prm->importValue($value));
			}
			
			$emptyValues = array(0, '0', false);
			
			foreach ($emptyValues as $value) {
				$this->assertFalse($prm->import(array('name' => $value)));
				$this->assertFalse($prm->importValue($value));
			}
		}
	}
?>