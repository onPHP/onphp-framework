<?php
	/* $Id$ */
	
	final class AssertTest extends TestCase
	{
		protected $backupGlobals = false;
		
		public function testTrue()
		{
			Assert::isTrue(true);
			
			try {
				Assert::isTrue(false);
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testFalse()
		{
			Assert::isFalse(false);
			
			try {
				Assert::isFalse(true);
				$this->fail();
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testFloat()
		{
			Assert::isFloat(4.2);
			Assert::isFloat('28.82');
			
			$this->nonFloatCheck(null);
		}
		
		public function testInteger()
		{
			Assert::isInteger(2006);
			Assert::isInteger(0);
			Assert::isInteger('095');
			
			$this->nonIntegerCheck(null);
			$this->nonIntegerCheck('1e9');
			$this->nonIntegerCheck(20.06);
			$this->nonIntegerCheck(acos(20.06));
			$this->nonIntegerCheck(log(0));
		}
		
		public function nonFloatCheck($string)
		{
			try {
				Assert::isFloat($string);
				$this->fail("'{$string}' is float!");
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function nonIntegerCheck($string)
		{
			try {
				Assert::isInteger($string);
				$this->fail("'{$string}' is integer!");
			} catch (WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testTernaryBase()
		{
			try {
				Assert::isTernaryBase($value = true);
				Assert::isTernaryBase($value = false);
				Assert::isTernaryBase($value = null);
				/* pass */
			} catch (WrongArgumentException $e) {
				$this->fail();
			}
		}

		public function testUuid()
		{
			$exampleUuid = '550e8400-e29b-41d4-a716-446655440000';
			try {
				Assert::isUuid($exampleUuid);
				Assert::isUuid(strtoupper($exampleUuid));
			} catch (WrongArgumentException $e) {
				$this->fail('uuid asserts working uncorrectly!');
			}

			$uncorrectlyUuid = '550j8400-e29b-41d4-a716-446655440000';
			try {
				Assert::isUuid($uncorrectlyUuid);

				$this->fail('uuid asserts working uncorrectly!');
			} catch (WrongArgumentException $e) {}

		}
	}
?>