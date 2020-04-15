<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\FloatRange;
use OnPHP\Tests\TestEnvironment\TestCase;

final class FloatRangeTest extends TestCase
{
	/**
	 * @dataProvider rangeDataProvider
	 * @doesNotPerformAssertions
	**/
	public function testCreation($min, $max, $throwsException)
	{
		if ($throwsException)
			$this->expectException(WrongArgumentException::class);

		$range = FloatRange::create($min, $max);
	} 

	public static function rangeDataProvider()
	{
		return array(
			array(
				1, 1, false
			),
			array(
				1, 222222222222222222222222222, false
			),
			array(
				0.1, 1, false
			),
			array(
				0, 1, false
			),
			array(
				2, 1, false
			)
		);
	}
}
?>