<?php
	/* $Id$ */
	
	final class AssertTest extends UnitTestCase
	{
		public function testTrue()
		{
			Assert::isTrue(true);

			try {
				Assert::isTrue(false);
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
		
		public function testFalse()
		{
			Assert::isFalse(false);
			
			try {
				Assert::isFalse(true);
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
		
		public function testInteger()
		{
			Assert::isInteger(2006);
			
			try {
				Assert::isInteger(20.06);
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
			
			try {
				Assert::isInteger(acos(20.06));
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
			
			try {
				Assert::isInteger(log(0));
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
		
		public function testTernaryBase()
		{
			try {
				Assert::isTernaryBase($value = true);
				Assert::isTernaryBase($value = false);
				Assert::isTernaryBase($value = null);
				$this->pass();
			} catch (WrongArgumentException $e) {
				$this->fail();
			}
		}
	}
?>