<?php
/* $Id$ */

namespace OnPHP\Tests\Core;

use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Base\Ternary;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Tests\TestEnvironment\TestCase;

final class SingletonTestInstance extends Singleton {/*_*/}

final class SingletonSingleArgumentTestInstance extends Singleton
{
	protected function __construct($arg1) { /*_*/ }
}

final class SingletonMultiArgumentTestInstance extends Singleton
{
	protected function __construct($arg1, $arg2, $arg3 = null) { /*_*/ }
}

final class SingletonTest extends TestCase
{
	const CLASS_NAME		= SingletonTestInstance::class;
	const SINGLE_CLASS_NAME	= SingletonSingleArgumentTestInstance::class;
	const MULTI_CLASS_NAME	= SingletonMultiArgumentTestInstance::class;
	
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
		$this->expectException(WrongArgumentException::class);
		Singleton::getInstance(Ternary::class);
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
	
	public function testMissingCleanup()
	{
		// cleaning up
		$this->expectException(MissingElementException::class);
		Singleton::dropInstance(self::SINGLE_CLASS_NAME);
	}
	
	public function testTooFewArguments()
	{
		$this->expectException(MissingElementException::class);
		Singleton::getInstance(self::SINGLE_CLASS_NAME);
	}
	
	public function testInstancesIsSame()
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