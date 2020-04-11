<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Date;
use OnPHP\Core\Base\IdentifiableObject;
use OnPHP\Main\Base\DateObjectComparator;
use OnPHP\Main\Base\ImmutableObjectComparator;
use OnPHP\Main\Base\SerializedObjectComparator;
use OnPHP\Tests\TestEnvironment\TestCase;

final class ComparatorTest extends TestCase
{
	/**
	 * @dataProvider serializedTestObjects
	**/
	public function testSerializedObjectComparator($one, $two, $result)
	{
		$this->assertEquals(
			$result,
			SerializedObjectComparator::me()->compare($one, $two)
		);
	}

	/**
	 * @dataProvider immutableTestObjects
	**/
	public function testImmutableObjectComparator($one, $two, $result)
	{
		$this->assertEquals(
			$result,
			ImmutableObjectComparator::me()->compare($one, $two)
		);
	}

	/**
	 * @dataProvider dateTestData
	**/
	public function testDateObjectComparator($one, $two, $result)
	{
		$this->assertEquals(
			$result,
			DateObjectComparator::me()->compare($one, $two)
		);
	}

	public static function serializedTestObjects()
	{
		$object = new ComparatorTestObject();
		$object->testVariable = 1;
		$object->anotherObject =  new ComparatorTest();

		$secondObject = clone $object;

		$modifiedObject = clone $secondObject;
		$modifiedObject->testVariable = 2;

		return
			array(
				array($object, $object, 0),
				array($object, $secondObject, 0),
				array($object, $modifiedObject, -1)
			);
	}

	public static function immutableTestObjects()
	{
		$object = new ComparatorTestObject();
		$object->testVariable = 1;
		$object->anotherObject =  new ComparatorTest();

		$secondObject = clone $object;

		$modifiedObject = clone $secondObject;
		$modifiedObject->testVariable = 2;

		$anotherModifiedObject = clone $modifiedObject;
		$anotherModifiedObject->id = 3;

		return
			array(
				array($object, $object, 0),
				array($object, $secondObject, 0),
				array($object, $modifiedObject, 0),
				array($modifiedObject, $anotherModifiedObject, -1)
			);
	}

	public static function dateTestData()
	{
		return
			array(
				array(Date::makeToday(), Date::makeToday(), 0),
				array(Date::makeToday(), Date::makeToday()->modify('-1 day'), 1),
				array(Date::makeToday()->modify('-1 day'), Date::makeToday(), -1)
			);
	}
}

final class ComparatorTestObject extends IdentifiableObject
{
	public $anotherObject	= null;
	public $testVariable	= null;
	public $id = 1;
}
?>