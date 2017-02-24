<?php

/* * *************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 * ************************************************************************* */

namespace Onphp\NsConverter\Flow;

use Onphp\CallbackLogicalObject;
use \Onphp\Form;
use \Onphp\NsConverter\AddUtils\CMDUtils;
use Onphp\NsConverter\EntitieProto\ConverterEntity;
use \Onphp\NsConverter\Utils\FormErrorWriter;
use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\NsConverter\Utils\PathListGetter;
use \Onphp\Primitive;

class AllCommand
{
	use OutputMsg, FormErrorWriter {
		OutputMsg::msg insteadof FormErrorWriter;
	}

	const CONFIG = '--config';
	const ACTION = '--action';

	public function run()
	{
		$form = $this->getForm()
			->import(CMDUtils::getOptionsList())
			->checkRules();

		if ($this->processFormError($form)) {
			return;
		}
		$this->msg("loading config");

		if (!$json = json_decode(file_get_contents($form->getValue(self::CONFIG)), true)) {
			$jsonError = json_last_error();
			if ($jsonError) {
				print "Json parse error {$jsonError}\n";
			} else {
				print "empty or incorrect json in --config\n";
			}
			return;
		}

		$configCommand = new ConfigCommand();
		$configCommand->setAction($form->getValue(self::ACTION));
		$configCommand->run($json);
	}

	/**
	 * @return Form
	 */
	private function getForm()
	{
		$form = Form::create()
			->add(Primitive::string(self::CONFIG)->required())
			->add(Primitive::plainChoice(self::ACTION)->setList(['scan', 'replace'])->required());
		$form->addRule('jsonConfig', new CallbackLogicalObject(function (Form $form) {
			$this->checkConfig($form);
			return true;
		}));
		return $form;
	}

	private function checkConfig(Form $form)
	{
		$path = $form->getValue(self::CONFIG);
		if (!file_exists($path)) {
			$form->markWrong(self::CONFIG, 'path does not exists');
		} elseif (!is_readable($path)) {
			$form->markWrong(self::CONFIG, 'path not readable');
		} elseif (!is_file($path)) {
			$form->markWrong(self::CONFIG, 'path not a file');
		}
	}
}
