<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Tests\TestEnvironment\ClassUtilsTestInterface;

class ClassUtilsTestClass implements ClassUtilsTestInterface
{
	private $object	= null;
	private $text 	= null;

	public static function create()
	{
		return new self;
	}

	public function getObject()
	{
		return $this->object;
	}

	public function setObject(ClassUtilsTestClass $object)
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
