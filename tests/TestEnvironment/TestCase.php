<?php

namespace OnPHP\Tests\TestEnvironment;

use ReflectionClass;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
	protected $backupGlobals = false;

	/**
	 * @param object $object
	 * @param string $parameter
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function getObjectProperty(object $object, string $parameter)
	{
		$class = new ReflectionClass($object);
		$property = $class->getProperty($parameter);
		$property->setAccessible(true);

		return $property->getValue($object);
	}

	protected function callObjectMethod($className, string $methodName, ...$args)
	{
		$class = new ReflectionClass($className);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);
		return $method->invokeArgs(
			is_object($className) ? $className : null,
			$args
		);
	}
}