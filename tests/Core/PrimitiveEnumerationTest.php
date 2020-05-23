<?php
	
namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitive;
use OnPHP\Core\OSQL\DataType;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group form
 */
final class PrimitiveEnumerationTest extends TestCase
{
	public function testIntegerValues()
	{
		$form =
			Form::create()->
			add(
				Primitive::enumeration('enum')->of(DataType::class)
			);

		$form->import(array('enum' => '4097'));

		$this->assertEquals($form->getValue('enum')->getId(), 0x001001);
		$this->assertSame($form->getValue('enum')->getId(), 0x001001);
	}

	public function testGetList()
	{
		$primitive = Primitive::enumeration('enum')->of(DataType::class);
		$enum = DataType::create(DataType::getAnyId());

		$this->assertEquals($primitive->getList(), $enum->getObjectList());

		$primitive->setDefault($enum);
		$this->assertEquals($primitive->getList(), $enum->getObjectList());

		$primitive->import(array('enum' => DataType::getAnyId()));
		$this->assertEquals($primitive->getList(), $enum->getObjectList());
	}
}
?>