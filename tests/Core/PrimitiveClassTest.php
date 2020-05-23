<?php
	
namespace OnPHP\Tests\Core;

use OnPHP\Core\Base\Enumeration;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Base\IdentifiableObject;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Form\Primitive;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group form
 */
final class PrimitiveClassTest extends TestCase
{
	public function test()
	{
		$prm = Primitive::clazz('name');

		$this->assertFalse($prm->import(array('name' =>'InExIsTaNtClass')));
		$this->assertFalse($prm->import(array('name' => "\0foo")));

		$this->assertTrue($prm->importValue(IdentifiableObject::class));
		$this->assertEquals($prm->getValue(), IdentifiableObject::class);
	}

	public function testOf()
	{
		$prm = Primitive::clazz('name');

		$this->assertFalse(
			$prm->
				of(Enumeration::class)->
				importValue(IdentifiableObject::class)
		);

		$this->assertTrue(
			$prm->
				of(Identifiable::class)->
				importValue(IdentifiableObject::class)
		);

		$this->assertTrue(
			$prm->
				of(IdentifiableObject::class)->
				importValue(IdentifiableObject::class)
		);
		
		
		$this->expectException(WrongArgumentException::class);
		$prm->of('InExIsNaNtClass');
	}
}
?>