<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Form\Primitives;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\UnsupportedMethodException;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Core\Form\Form;

/**
 * @ingroup Primitives
**/
final class PrimitiveFormsList extends PrimitiveForm
{
	protected $value = array();

	/**
	 * @return PrimitiveFormsList
	**/
	public function clean()
	{
		parent::clean();

		$this->value = array();

		return $this;
	}

	public function setComposite($composite = true)
	{
		throw new UnsupportedMethodException(
			'composition is not supported for lists'
		);
	}

	public function getInnerErrors()
	{
		$result = array();

		foreach ($this->getValue() as $id => $form) {
			if ($errors = $form->getInnerErrors())
				$result[$id] = $errors;
		}

		return $result;
	}

	public function import($scope)
	{
		if (!$this->proto)
			throw new WrongStateException(
				"no proto defined for PrimitiveFormsList '{$this->name}'"
			);

		if (!BasePrimitive::import($scope))
			return null;

		if (!is_array($scope[$this->name]))
			return false;

		$error = false;

		$this->value = array();

		foreach ($scope[$this->name] as $id => $value) {
			$this->value[$id] =
				$this->proto->makeForm()->
					import($value);

			if ($this->value[$id]->getErrors())
				$error = true;
		}

		return !$error;
	}

	public function importValue($value)
	{
		if ($value !== null)
			Assert::isArray($value);
		else
			return null;

		$result = true;

		$resultValue = array();

		foreach ($value as $id => $form) {
			Assert::isInstance($form, Form::class);

			$resultValue[$id] = $form;

			if ($form->getErrors())
				$result = false;
		}

		$this->value = $resultValue;

		return $result;
	}

	public function exportValue()
	{
		if (!$this->isImported())
			return null;

		$result = array();

		foreach ($this->value as $id => $form) {
			$result[$id] = $form->export();
		}

		return $result;
	}
}
?>
