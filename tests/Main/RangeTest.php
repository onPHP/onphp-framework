<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\Range;
use OnPHP\Tests\TestEnvironment\TestCase;

final class RangeTest extends TestCase
{
	/**
	 * @doesNotPerformAssertions
	 */
	public function testBothInteger()
	{
		Range::create(1, 1);
	}
	
	
	public function testMaxIsBiggerThanInteger()
	{
		$this->expectException(WrongArgumentException::class);
		Range::create(1, 222222222222222222222222222);
	}
	
	public function testMinIsFloat()
	{
		$this->expectException(WrongArgumentException::class);
		Range::create(0.1, 1);
	}
	
	/**
	 * @doesNotPerformAssertions
	 */
	public function testMinIsZero()
	{
		Range::create(0, 1);
	}
	
	/**
	 * @doesNotPerformAssertions
	 */
	public function testMinIsBiggerThanMax()
	{
		Range::create(2, 1);
	}
}
?>