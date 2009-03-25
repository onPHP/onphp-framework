<?php
	/* $Id$ */
	
	final class SingletonTestInstance extends Singleton {/*_*/}
	
	final class SingletonSingleArgumentTestInstance extends Singleton
	{
		protected function __construct($arg1) { /*_*/ }
	}
	
	final class SingletonMultiArgumentTestInstance extends Singleton
	{
		protected function __construct($arg1, $arg2, $arg3 = null) { /*_*/ }
	}
	
	final class SingletonTest extends UnitTestCase
	{
		const CLASS_NAME		= 'SingletonTestInstance';
		const SINGLE_CLASS_NAME	= 'SingletonSingleArgumentTestInstance';
		const MULTI_CLASS_NAME	= 'SingletonMultiArgumentTestInstance';
		
		public function testFactoryLikeCall()
		{
			$this->assertSameInstances(
				self::CLASS_NAME,
				Singleton::getInstance(self::CLASS_NAME),
				Singleton::getInstance(self::CLASS_NAME)
			);
		}
		
		public function testNonSingletonChilds()
		{
			try {
				Singleton::getInstance('Ternary');
				$this->fail();
			} catch (WrongArgumentException $e) {
				$this->pass();
			}
		}
		
		public function testCreationProhibition()
		{
			$child = new ReflectionClass(self::CLASS_NAME);
			
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
			$this->assertSameInstances(
				self::SINGLE_CLASS_NAME,
				Singleton::getInstance(self::SINGLE_CLASS_NAME, 'val1'),
				Singleton::getInstance(self::SINGLE_CLASS_NAME, 'val2')
			);
			
			$this->assertSameInstances(
				self::MULTI_CLASS_NAME,
				Singleton::getInstance(self::MULTI_CLASS_NAME, 'val1', 'val2', 'val3'),
				Singleton::getInstance(self::MULTI_CLASS_NAME, 'val1', 'val2')
			);
		}
		
		private function assertSameInstances(
			$className,
			Singleton $instance1,
			Singleton $instance2
		)
		{
			$this->assertTrue($instance1 === $instance2);
			
			$all = Singleton::getAllInstances();
			$this->assertTrue($instance1 === $all[$className]); 
		}
	}
?>