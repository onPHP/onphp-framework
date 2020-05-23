<?php

namespace OnPHP\Tests\Core;

use OnPHP\Core\Base\Date;
use OnPHP\Core\Form\Filter;
use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\FormCollection;
use OnPHP\Core\Form\FormField;
use OnPHP\Core\Form\Primitive;
use OnPHP\Core\Form\Primitives\PrimitiveRange;
use OnPHP\Core\Logic\Expression;
use OnPHP\Main\Net\HttpUrl;
use OnPHP\Tests\TestEnvironment\TestCase;
use OnPHP\Tests\Meta\Business\TestCity;

/**
 * @group core
 * @group form
 */
final class FormTest extends TestCase
{
	public function testRange()
	{
		$scope = array(
			'test' => array(
				PrimitiveRange::MIN => '42',
				PrimitiveRange::MAX => '64',
			)
		);

		$form =
			Form::create()->add(
				Primitive::range('test')
			)->
			import($scope);

		$this->assertTrue(
			$form->get('test')->getValue()->getMin()
			=== $form->get('test')->getMin()
		);

		$this->assertTrue(
			$form->get('test')->getValue()->getMax()
			=== $form->get('test')->getMax()
		);

		$this->assertTrue(
			$form->get('test')->getMin() == 42
		);

		$this->assertTrue(
			$form->get('test')->getMax() == 64
		);
	}

	public function testSafeValues()
	{
		$prm = Primitive::date('date');
		$date = Date::create('2005-02-19');

		$prm->import(
			array('date' => '2005-02-19')
		);

		$this->assertTrue($prm->isImported());

		$this->assertTrue(
			$prm->getSafeValue() == $date
		);

		$prm = Primitive::date('date')->setDefault(
			$date
		);

		$prm->import(
			array('date' => 'omgEvilInput')
		);

		$this->assertTrue($prm->isImported());

		$this->assertTrue(
			$prm->getSafeValue() === null
		);
	}

	public function testErrors()
	{
		$form = Form::create()->
			add(
				Primitive::ternary('flag')->
					setFalseValue('0')->
					setTrueValue('1')
			)->
			add(Primitive::integer('old')->required())->
			addRule('someRule', Expression::between(FormField::create('old'), '18', '35'));

		//empty import
		$form->import(array())->checkRules();

		//checking
		$expectingErrors = array('old' => Form::MISSING, 'someRule' => Form::WRONG);
		$this->assertEquals($expectingErrors, $form->getErrors());
		$this->assertEquals(Form::MISSING, $form->getError('old'));
		$this->assertEquals(Form::WRONG, $form->getError('someRule'));
		$this->assertTrue($form->hasError('old'));
		$this->assertFalse($form->hasError('flag'));

		//drop errors
		$form->dropAllErrors();
		$this->assertEquals(array(), $form->getErrors());

		//import wrong data
		$form->clean()->importMore(array('flag' => '3', 'old' => '17'))->checkRules();

		//checking
		$expectingErrors = array('flag' => Form::WRONG, 'someRule' => Form::WRONG);
		$this->assertEquals($expectingErrors, $form->getErrors());
		$this->assertTrue($form->hasError('someRule'));

		//marking good and custom check errors
		$form->markGood('someRule')->markCustom('flag', 3);
		$this->assertEquals(array('flag' => 3), $form->getErrors());
		$this->assertFalse($form->hasError('someRule'));
		$this->assertNull($form->getError('someRule'));
		$this->assertEquals(3, $form->getError('flag'));

		//import right data
		$form->
			dropAllErrors()->
			clean()->
			importMore(array('flag' => '1', 'old' => '35'));

		//checking
		$this->assertEquals(array(), $form->getErrors());
	}

	public function testDisableImportFiltering()
	{
		$form = Form::create()->
			add(
				Primitive::string("name")->
					addImportFilter(Filter::trim())
			);

		$form->import(array("name" => "test "));

		$this->assertEquals("test", $form->getValue("name"));

		$form->clean()->disableImportFiltering();

		$form->import(array("name" => "test "));

		$this->assertEquals("test ", $form->getValue("name"));
	}

	public function testFormCollection()
	{
		$collection = 
			FormCollection::create(
				TestCity::proto()->makeForm()->
					drop('id')
			);


		$url = HttpUrl::create()->parse('http://i.would.like.to.create.cities/?name[77]=Moscow&capital[77]=1&large[77]=1&name[50]=Krasnogorsk&name[78]=Piter&large[78]=1');
		parse_str($url->getQuery(), $getArray);

		$collection->import($getArray);

		foreach ($collection as $number => $form) {
			switch ($number) {
				case 77:
					$this->assertEquals('Moscow', $form->getValue('name'));
					$this->assertTrue($form->getValue('capital'));
					$this->assertTrue($form->getValue('large'));

					break;

				case 78:
					$this->assertEquals('Piter', $form->getValue('name'));
					$this->assertFalse($form->getValue('capital'));
					$this->assertTrue($form->getValue('large'));

					break;

				case 50:
					$this->assertEquals('Krasnogorsk', $form->getValue('name'));
					$this->assertFalse($form->getValue('capital'));
					$this->assertFalse($form->getValue('large'));

					break;

				default:
					$this->assertTrue(false);
					break;
			}
		}
	}
}
?>