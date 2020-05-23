<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Primitive;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group form
 */
final class PrimitiveNumberTest extends TestCase
{
	public function testInteger()
	{
		$prm = Primitive::integer('int');

		$this->assertTrue($prm->importValue(0));

		$this->assertFalse($prm->importValue('abc'));
	}
}
?>