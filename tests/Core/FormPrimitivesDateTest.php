<?php
	
namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitive;
use OnPHP\Core\Form\Primitives\PrimitiveDate;
use OnPHP\Tests\TestEnvironment\TestCase;

final class FormPrimitivesDateTest extends TestCase
{
	const VALID_DAY		= '22';
	const VALID_MONTH	= '03';
	const VALID_YEAR	= '2009';

	const INVALID_DAY	= '33';
	const INVALID_MONTH	= '13';
	const INVALID_YEAR	= '2009';

	public function testValidScope()
	{
		$data =
			array(
				PrimitiveDate::DAY => self::VALID_DAY,
				PrimitiveDate::MONTH => self::VALID_MONTH,
				PrimitiveDate::YEAR => self::VALID_YEAR,
			);

		$scope = array(
			'test' => $data
		);

		$this->processValidScopeBy($scope, $data);

		$data =
			self::VALID_YEAR
			."-".self::VALID_MONTH
			.'-'.self::VALID_DAY;

		$scope = array(
			'test' => $data
		);

		$this->processValidScopeBy($scope, $data);
	}

	public function testInvalidScope()
	{
		$data =
			array(
				PrimitiveDate::DAY => self::INVALID_DAY,
				PrimitiveDate::MONTH => self::INVALID_MONTH,
				PrimitiveDate::YEAR => self::INVALID_YEAR,
			);

		$scope = array(
			'test' => $data
		);

		$this->processInvalidBy($scope, $data);

		$data =
			self::INVALID_YEAR
			."-".self::INVALID_MONTH
			.'-'.self::INVALID_DAY;

		$scope = array(
			'test' => $data
		);

		$this->processInvalidBy($scope, $data);
	}

	public function testEmptyScope()
	{
		$this->processEmptyScope(false);
	}

	public function testEmptyScopeWithRequired()
	{
		$this->processEmptyScope(true);
	}

	protected function processValidScopeBy($scope, $data)
	{
		$form =
			Form::create()->add(
				Primitive::date('test')
			)->
			import($scope);

		$this->assertEquals(
			$form->getValue('test')->getDay(),
			(int) self::VALID_DAY
		);

		$this->assertEquals(
			$form->getValue('test')->getMonth(),
			(int) self::VALID_MONTH
		);

		$this->assertEquals(
			$form->getValue('test')->getYear(),
			(int) self::VALID_YEAR
		);

		$this->assertEquals(
			$form->getRawValue('test'),
			$data
		);

		$this->assertEquals(
			$form->get('test')->isImported(),
			true
		);

		$this->assertEquals(
			$form->getErrors(),
			array()
		);
	}

	protected function processInvalidBy($scope, $data)
	{
		$form =
			Form::create()->add(
				Primitive::date('test')
			)->
			import($scope);

		$this->assertEquals(
			$form->getValue('test'),
			null
		);

		$this->assertEquals(
			$form->getRawValue('test'),
			$data
		);

		$this->assertEquals(
			$form->get('test')->isImported(),
			true
		);

		$this->assertEquals(
			$form->getErrors(),
			array(
				'test' => Form::WRONG,
			)
		);
	}

	protected function processEmptyScope($required)
	{
		$data =
			array(
				PrimitiveDate::DAY => '',
				PrimitiveDate::MONTH => '',
				PrimitiveDate::YEAR => '',
			);

		$scope = array(
			'test' => $data
		);

		$primitive = Primitive::date('test');
		if ($required)
			$primitive->setRequired(true);

		$form =
			Form::create()->
			add($primitive)->
			import($scope);

		$this->assertEquals(
			$form->getValue('test'),
			null
		);

		if ($required)
			$this->assertEquals(
				$form->getErrors(),
				array(
					'test' => Form::MISSING,
				)
			);
		else
			$this->assertEquals(
				$form->getErrors(),
				array()
			);

		$this->assertEquals(
			$form->get('test')->isImported(),
			true
		);

		$this->assertEquals(
			$form->getRawValue('test'),
			$data
		);
	}
}
?>