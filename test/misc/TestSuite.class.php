<?php
	final class TestSuite extends PHPUnit_Framework_TestSuite
	{
		public function setUp()
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
		
		public function tearDown()
		{
			echo "\n";
		}
	}
?>