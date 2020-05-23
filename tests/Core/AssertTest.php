<?php

namespace OnPHP\Tests\Core;

use OnPHP\Tests\TestEnvironment\TestCase;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @group core
 */
final class AssertTest extends TestCase
{
	protected $backupGlobals = false;
	
	/**
	* @doesNotPerformAssertions
	*/
	public function testTrue()
	{
		Assert::isTrue(true);
	}
	
	public function testTrueFail()
	{
		$this->expectException(WrongArgumentException::class);
		Assert::isTrue(false);
	}
	
	/**
	* @doesNotPerformAssertions
	*/
	public function testFalse()
	{
		Assert::isFalse(false);
	}
	
	public function testFalseFail()
	{
		$this->expectException(WrongArgumentException::class);
		Assert::isFalse(true);
	}
	
	/**
	* @doesNotPerformAssertions
	*/
	public function testFloat()
	{
		Assert::isFloat(4.2);
		Assert::isFloat('28.82');
	}
	
	public function testFloatFail()
	{
		$this->expectException(WrongArgumentException::class);
		Assert::isFloat(null);
	}
	
	/**
	* @doesNotPerformAssertions
	*/
	public function testInteger() {
		Assert::isInteger(2006);
		Assert::isInteger(0);
		Assert::isInteger('095');
		Assert::isInteger('1e9');
	}

	public function testIntegerWithNullFail() {
		$this->expectException(WrongArgumentException::class);
		Assert::isInteger(null);
	}
	
	public function testIntegerWithFloatFail() {
		$this->expectException(WrongArgumentException::class);
		Assert::isInteger(20.06);
	}
	
	public function testIntegerWithNANFail() {
		$this->expectException(WrongArgumentException::class);
		Assert::isInteger(acos(20.06));
	}
	
	public function testIntegerWithINFFail() {
		$this->expectException(WrongArgumentException::class);
		Assert::isInteger(log(0));
	}
	
	/**
	* @doesNotPerformAssertions
	*/
	public function testTernaryBase()
	{
		Assert::isTernaryBase(true);
		Assert::isTernaryBase(false);
		Assert::isTernaryBase(null);
	}
	
	public function testTernaryBaseWithStringFail()
	{
		$this->expectException(WrongArgumentException::class);
		Assert::isTernaryBase('true');
	}
}
?>