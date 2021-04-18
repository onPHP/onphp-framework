<?php

namespace OnPHP\Tests\TestEnvironment\ClassUtils;

class TestClassLazyLoad extends TestClass
{
	public $name        = null;
	private $testId     = null;
	private $test       = null;

	public static function create()
	{
		return new static;
	}

	public function getTestId()
	{
		return $this->testId;
	}

	public function setTestId($testId)
	{
		$this->testId = $testId;

		return $this;
	}

	public function getTest()
	{
		if (
			null === $this->test
			&& null !== $this->testId
		) {
			$this->test = (new self)->setTestId($this->testId);
		}

		return $this->test;
	}

	public function setTest(TestClassLazyLoad $class)
	{
		$this->test = $class;
		$this->testId = $class->getTestId();

		return $this;
	}

	public function dropTest()
	{
		$this->testId = null;
		$this->test = null;

		return $this;
	}
}
