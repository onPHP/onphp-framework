<?php

namespace OnPHP\Tests\TestEnvironment;

abstract class TestCaseDAO extends TestCaseDB
{
	public function setUp(): void
	{
		parent::setUp();

		$this->getDBCreator()->dropDB(true)->createDB();
	}
}
?>