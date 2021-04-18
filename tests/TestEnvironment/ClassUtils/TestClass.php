<?php

namespace OnPHP\Tests\TestEnvironment\ClassUtils;

class TestClass implements TestInterface
{
	use TestTraitChild;

	private $object	= null;
	private $text 	= null;

	public static function create()
	{
		return new static;
	}

	public function getObject()
	{
		return $this->object;
	}

	public function setObject(TestClass $object)
	{
		$this->object = $object;

		return $this;
	}

	public function dropObject()
	{
		$this->object = null;

		return $this;
	}

	public function getText()
	{
		return $this->text;
	}

	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}
}
