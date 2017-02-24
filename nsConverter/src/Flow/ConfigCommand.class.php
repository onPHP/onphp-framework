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

use Onphp\Assert;
use \Onphp\Form;
use Onphp\NsConverter\Business\ActionEnum;
use Onphp\NsConverter\EntitieProto\ConverterEntity;
use Onphp\NsConverter\Utils\ClassStorage;
use \Onphp\NsConverter\Utils\FormErrorWriter;
use Onphp\NsConverter\Utils\NamespaceUtils;
use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\NsConverter\Utils\PathListGetter;
use Onphp\UnimplementedFeatureException;
use Onphp\WrongStateException;

class ConfigCommand
{
	use OutputMsg, FormErrorWriter {
		OutputMsg::msg insteadof FormErrorWriter;
	}

	private $action;

	public function setAction($action)
	{
		$this->action = $action;
	}

	public function run(array $config)
	{
		Assert::isNotNull($this->action, 'setAction first');

		$form = ConverterEntity::me()->makeForm()
			->import($config)
			->checkRules();

		if (!ConverterEntity::me()->validate(null, $form)) {
			$this->msg("config validation errrors:");
			$this->processFormError($form);
			return;
		}

		$classStorage = $this->spawnClassStorage();

		if ($this->action == 'scan') {
			$this->doScan($form, $classStorage);
		} elseif ($this->action == 'replace') {
			$this->doReplace($form, $classStorage);
		} else {
			throw new UnimplementedFeatureException("not expected --action");
		}
	}

	private function doScan(Form $form, ClassStorage $classStorage)
	{
		$scanCommand = new NewScanCommand();
		$scanCommand->run($form, $classStorage);
		file_put_contents($this->getConfFileName($form), $classStorage->export(false));
		print "scan finished success\n";
	}

	private function doReplace(Form $form, ClassStorage $classStorage)
	{
		$confPath = $this->getConfFileName($form);
		if (!file_exists($confPath)) {
			print "do action --scan first";
			return;
		}
		$classStorage->import(file_get_contents($confPath));

		$replaceCommand = new NewReplaceCommand();
		$replaceCommand->run($form, $classStorage);
	}

	private function getConfFileName(Form $form)
	{
		$path = $form->getValue('conf');
		if (file_exists($path)) {
			if (is_dir($path)) {
				return $path.'/scan.ns';
			} elseif (is_file($path)) {
				return $path;
			} else {
				throw new WrongStateException("conf path not a dir and not a file");
			}
		}
		return $path;
	}

	/**
	 * @return ClassStorage
	 */
	private function spawnClassStorage()
	{
		$classStorage = new ClassStorage();
		$classStorage->import(file_get_contents(dirname(dirname(__DIR__)).'/data/php.ns'));

		return $classStorage;
	}
}
