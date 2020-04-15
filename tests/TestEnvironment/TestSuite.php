<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\Cache\Cache;
use OnPHP\Tests\AllTests;
use PHPUnit\Framework\TestSuite as PHPUnitTestSuite;

final class TestSuite extends PHPUnitTestSuite
{
	public function setUp(): void
	{
		if (AllTests::$workers) {
			$worker = array_pop(AllTests::$workers);
			echo "\nProcessing with {$worker}\n";
			Cache::dropWorkers();
			Cache::setDefaultWorker($worker);
		} else {
			$this->markTestSuiteSkipped('No more workers available.');
		}
	}

	public function tearDown(): void
	{
		echo "\n";
	}
}
?>