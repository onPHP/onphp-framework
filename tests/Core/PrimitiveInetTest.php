<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Primitive;
use OnPHP\Tests\TestEnvironment\TestCase;

final class PrimitiveInetTest extends TestCase
{
	public function testInet()
	{
		$prm = Primitive::inet('inet');

		$this->assertTrue($prm->importValue('127.0.0.1'));
		$this->assertTrue($prm->importValue('254.254.254.254'));
		$this->assertTrue($prm->importValue('0.0.0.0'));

		$this->assertFalse($prm->importValue('10.0.0'));
		$this->assertFalse($prm->importValue('42.42.42.360'));
		$this->assertFalse($prm->importValue('10.0.256'));

	}
}
?>