<?php
/****************************************************************************
 *   Copyright (C) 2013 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Core\Form;

use OnPHP\Core\Base\Assert;

final class FormCollection implements \Iterator
{
	/**
	 * @var Form
	 */
	private $sampleForm = null;

	private $primitiveNames = array();

	private $imported = false;

	private $formList = array();

	/**
	 * 
	 * @param Form $sample
	 * @return FormCollection
	 */
	public static function create(Form $sample)
	{
		return new self($sample);
	}


	public function __construct(Form $sample)
	{
		$this->sampleForm = $sample;
	}

	/**
	 * 
	 * @param array $scope 
	 * from http request
	 * looks like foo[1]=42&bar[1]=test&foo[2]=44&bar[2]=anothertest
	 */
	public function import(array $scope)
	{
		$this->imported = true;

		foreach ($scope as $name => $paramList) {

			/**
			 *@var array $paramList
			 * looks like array(1 => 42, 2 => 44)
			 */
			Assert::isArray($paramList);

			foreach ($paramList as $key => $value) {
				if (!isset($this->formList[$key])) {
					$this->formList[$key] = clone $this->sampleForm;
				}
					$this->formList[$key]->importMore(array($name => $value));
			}
		}

		reset($this->formList);

		return $this;
	}

	public function current()
	{
		Assert::isTrue($this->imported, "Import scope in me before try to iterate");

		return current($this->formList);
	}

	public function key()
	{
		Assert::isTrue($this->imported, "Import scope in me before try to iterate");

		return key($this->formList);
	}

	public function next()
	{
		Assert::isTrue($this->imported, "Import scope in me before try to iterate");

		return next($this->formList);
	}

	public function rewind()
	{
		Assert::isTrue($this->imported, "Import scope in me before try to iterate");

		return reset($this->formList);
	}

	public function valid()
	{
		Assert::isTrue($this->imported, "Import scope in me before try to iterate");

		return (key($this->formList) !== null);
	}
}
?>