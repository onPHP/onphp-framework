<?php
	/* $Id$ */
	
	final class TernaryTest extends TestCase
	{
		public function testSpawn()
		{
			foreach (
				array(
					't' => true,
					'f' => false,
					'n' => null
				)
				as $value => $result
			) {
				$this->assertEquals(
					Ternary::spawn($value, 't', 'f', 'n')->getValue(),
					$result
				);
			}
			
			try {
				Ternary::spawn('bleh', 't', 'f', 'n');
				
				$this->fail();
			} catch (WrongArgumentException $e) {/*_*/}
		}
		
		public function testAccessors()
		{
			$trinity = Ternary::create(null);
			
			$this->assertTrue($trinity->isNull());
			$this->assertEquals($trinity->getValue(), null);
			$this->assertEquals($trinity->setNull()->getValue(), null);
			$this->assertEquals($trinity->toString(), 'null');
		}
		
		public function testDecide()
		{
			$trinity = Ternary::create(false);
			
			$this->assertEquals(
				$trinity->decide('true', 'false', 'null'),
				'false'
			);
		}
	}
?>