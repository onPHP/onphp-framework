<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\CommandLine;

use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Form\Form;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;

final class ArgumentParser extends Singleton
{
	private $form = null;
	private $result = null;

	/**
	 * @return ArgumentParser
	**/
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	/**
	 * @return ArgumentParser
	**/
	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	/**
	 * @return Form
	**/
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * @return ArgumentParser
	**/
	public function parse()
	{
		Assert::isNotNull($this->form);

		$long = FormToArgumentsConverter::getLong($this->form);

		// NOTE: stupid php, see man about long params
		if (empty($long))
			$this->result = getopt(
				FormToArgumentsConverter::getShort($this->form)
			);
		else
			$this->result = getopt(
				FormToArgumentsConverter::getShort($this->form),
				$long
			);

		return $this;
	}

	/**
	 * @return ArgumentParser
	**/
	public function validate()
	{
		Assert::isNotNull($this->result);

		$this->form->import($this->result);

		if ($errors = $this->form->getErrors())
			throw new WrongArgumentException(
				"\nArguments wrong:\n"
				.print_r($errors, true)
			);

		return $this;
	}
}
?>