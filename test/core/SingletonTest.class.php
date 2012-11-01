<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class SingletonTestInstance extends \Onphp\Singleton {/*_*/}
	
	final class SingletonSingleArgumentTestInstance extends \Onphp\Singleton
	{
		protected function __construct($arg1) { /*_*/ }
	}
	
	final class SingletonMultiArgumentTestInstance extends \Onphp\Singleton
	{
		protected function __construct($arg1, $arg2, $arg3 = null) { /*_*/ }
	}
	
	final class SingletonTest extends TestCase
	{
		const CLASS_NAME		= '\Onphp\Test\SingletonTestInstance';
		const SINGLE_CLASS_NAME	= '\Onphp\Test\SingletonSingleArgumentTestInstance';
		const MULTI_CLASS_NAME	= '\Onphp\Test\SingletonMultiArgumentTestInstance';
		
		public function testFactoryLikeCall()
		{
			$this->assertSameInstances(
				self::CLASS_NAME,
				\Onphp\Singleton::getInstance(self::CLASS_NAME),
				\Onphp\Singleton::getInstance(self::CLASS_NAME)
			);
		}
		
		public function testNonSingletonChilds()
		{
			try {
				\Onphp\Singleton::getInstance('\Onphp\Ternary');
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				/* pass */
			}
		}
		
		public function testCreationProhibition()
		{
			$child = new \ReflectionClass(self::CLASS_NAME);
			
			$this->assertFalse(
				$child->getMethod('__construct')->isPublic()
			);
			$this->assertTrue(
				$child->getMethod('__construct')->isProtected()
			);
			
			$this->assertTrue(
				$child->getMethod('__sleep')->isFinal()
			);
			$this->assertTrue(
				$child->getMethod('__sleep')->isPrivate()
			);
			
			$this->assertTrue(
				$child->getMethod('__clone')->isFinal()
			);
			$this->assertTrue(
				$child->getMethod('__clone')->isPrivate()
			);
		}
		
		public function testArguments()
		{
			// cleaning up
			try {
				\Onphp\Singleton::dropInstance(self::SINGLE_CLASS_NAME);
			} catch (\Onphp\MissingElementException $e) {
				// that's ok for the first pass
			}
			
			try {
				\Onphp\Singleton::getInstance(self::SINGLE_CLASS_NAME);
				$this->fail();
			} catch (\Onphp\BaseException $e) {
				// pass
			}
			
			$this->assertSameInstances(
				self::SINGLE_CLASS_NAME,
				\Onphp\Singleton::getInstance(self::SINGLE_CLASS_NAME, 'val1'),
				\Onphp\Singleton::getInstance(self::SINGLE_CLASS_NAME, 'val2')
			);
			
			$this->assertSameInstances(
				self::MULTI_CLASS_NAME,
				\Onphp\Singleton::getInstance(self::MULTI_CLASS_NAME, 'val1', 'val2', 'val3'),
				\Onphp\Singleton::getInstance(self::MULTI_CLASS_NAME, 'val1', 'val2')
			);
		}
		
		private function assertSameInstances(
			$className,
			\Onphp\Singleton $instance1,
			\Onphp\Singleton $instance2
		)
		{
			$this->assertTrue($instance1 === $instance2);
			
			$all = \Onphp\Singleton::getAllInstances();
			$this->assertTrue($instance1 === $all[$className]);
		}
	}
?>