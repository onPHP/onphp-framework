<?php
	/* $Id$ */
	
	final class BinaryTest extends TestCase
	{
		public function testBinary()
		{
			$binary = new Binary();
			
			$binary->setMin(5)->setMax(10);
			
			try {
				$binary->setValue('23');
				
				$this->fail();
			} catch (OutOfRangeException $e) {/*_*/}
			
			try {
				$binary->setValue('way too long value');
				
				$this->fail();
			} catch (OutOfRangeException $e) {/*_*/}
			
			$value = '12345'.chr(0).'123';
			
			$this->assertEquals(
				$binary->setValue('12345'.chr(0).'123')->getValue(),
				$value
			);
			
			try {
				$binary->setValue(new stdClass());
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
		}
	}
?>