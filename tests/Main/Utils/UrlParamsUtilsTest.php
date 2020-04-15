<?php

namespace OnPHP\Tests\Main\Utils;

use OnPHP\Core\Exception\BaseException;
use OnPHP\Main\Util\UrlParamsUtils;
use OnPHP\Tests\TestEnvironment\TestCase;

final class UrlParamsUtilsTest extends TestCase
{
	public function testAnyDeepLvl()
	{
		$scope = array(
			'foo' => array('foo' => array('foo' => '@bar')),
			'bar' => array('@bar' => array('bar' => "foo[]я")),
			'fo' => array(
				array('o', 'ba', 'r'),
			)
		);

		$this->assertEquals(
			array(
				'foo[foo][foo]' => '@bar',
				'bar[@bar][bar]' => 'foo[]я',
				'fo[0][0]' => 'o',
				'fo[0][1]' => 'ba',
				'fo[0][2]' => 'r',
			),
			UrlParamsUtils::toParamsList($scope)
		);

		$this->assertEquals(
			'foo[foo][foo]=%40bar&bar[%40bar][bar]=foo%5B%5D%D1%8F&fo[0][0]=o&fo[0][1]=ba&fo[0][2]=r',
			UrlParamsUtils::toString($scope)
		);
	}
}
?>