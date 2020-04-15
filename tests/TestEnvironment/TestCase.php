<?php

namespace OnPHP\Tests\TestEnvironment;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
	protected $backupGlobals = false;
}
?>