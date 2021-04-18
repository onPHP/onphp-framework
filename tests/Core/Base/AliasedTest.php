<?php

namespace OnPHP\Tests\Core\Base;

use OnPHP\Core\Base\Aliased;
use OnPHP\Tests\TestEnvironment\Base\AliasedTestInstance;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 */
final class AliasedTest extends TestCase
{
	const INTERFACE_NAME        = Aliased::class;
	const ALIASED_CLASS_NAME    = AliasedTestInstance::class;

	/**
	 * @throws \ReflectionException
	 */
	public function testInterface()
	{
		$interface = new \ReflectionClass(self::INTERFACE_NAME);
		$this->assertTrue($interface->isInterface());
		$this->assertTrue($interface->hasMethod('getAlias'));
		$this->assertTrue($interface->getMethod('getAlias')->isPublic());
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testClass()
	{
		$class = new \ReflectionClass(self::ALIASED_CLASS_NAME);
		$this->assertTrue($class->implementsInterface(self::INTERFACE_NAME));
		$this->assertTrue($class->hasMethod('getAlias'));
		$this->assertTrue($class->getMethod('getAlias')->isPublic());
	}
}