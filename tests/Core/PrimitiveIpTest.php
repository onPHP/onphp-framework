<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Primitive;
use OnPHP\Main\Net\Ip\IpAddress;
use OnPHP\Tests\TestEnvironment\TestCase;

final class PrimitiveIpTest extends TestCase
{
	public function testBase()
	{
		$prm = Primitive::ipAddress('ip');

		$this->assertTrue($prm->importValue('127.0.0.1'));
		$this->assertTrue($prm->importValue('254.254.254.254'));

		$this->assertTrue($prm->importValue('0.0.0.0'));

		$this->assertFalse($prm->importValue('10.0.0'));
		$this->assertFalse($prm->importValue('42.42.42.360'));
		$this->assertFalse($prm->importValue('10.0.256'));

		$prmWithDefault =
			Primitive::ipAddress('ip')->setDefault(IpAddress::create('42.42.42.42'));

		$this->assertEquals(
			$prmWithDefault->getActualValue()->toString(),
			'42.42.42.42'
		);

		$prmWithDefault->import(array('ip' => '43.43.43.43'));

		$this->assertEquals(
			$prmWithDefault->getActualValue()->toString(),
			'43.43.43.43'
		);

		$prmWithDefault->importValue(IpAddress::create('8.8.8.8')); //google public dns ;)

		$this->assertEquals(
			$prmWithDefault->getActualValue()->toString(),
			'8.8.8.8'
		);
	}
}
?>