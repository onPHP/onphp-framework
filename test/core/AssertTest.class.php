<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class AssertTest extends TestCase
	{
		protected $backupGlobals = false;
		
		public function testTrue()
		{
			\Onphp\Assert::isTrue(true);
			
			try {
				\Onphp\Assert::isTrue(false);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testFalse()
		{
			\Onphp\Assert::isFalse(false);
			
			try {
				\Onphp\Assert::isFalse(true);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testFloat()
		{
			\Onphp\Assert::isFloat(4.2);
			\Onphp\Assert::isFloat('28.82');
			
			$this->nonFloatCheck(null);
		}
		
		public function testInteger()
		{
			\Onphp\Assert::isInteger(2006);
			\Onphp\Assert::isInteger(0);
			\Onphp\Assert::isInteger('095');
			
			$this->nonIntegerCheck(null);
			$this->nonIntegerCheck('1e9');
			$this->nonIntegerCheck(20.06);
			$this->nonIntegerCheck(acos(20.06));
			$this->nonIntegerCheck(log(0));
		}
		
		public function nonFloatCheck($string)
		{
			try {
				\Onphp\Assert::isFloat($string);
				$this->fail("'{$string}' is float!");
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function nonIntegerCheck($string)
		{
			try {
				\Onphp\Assert::isInteger($string);
				$this->fail("'{$string}' is integer!");
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testTernaryBase()
		{
			try {
				\Onphp\Assert::isTernaryBase($value = true);
				\Onphp\Assert::isTernaryBase($value = false);
				\Onphp\Assert::isTernaryBase($value = null);
				/* pass */
			} catch (\Onphp\WrongArgumentException $e) {
				$this->fail();
			}
		}
	}
?>