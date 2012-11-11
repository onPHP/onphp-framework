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

use \Onphp\Form;
use \Onphp\NsConverter\AddUtils\CallbackLogicalObjectSuccess;
use \Onphp\NsConverter\AddUtils\CMDUtils;
use \Onphp\NsConverter\Business\NsClass;
use \Onphp\NsConverter\Business\NsConstant;
use \Onphp\NsConverter\Utils\ClassStorage;
use \Onphp\NsConverter\Utils\FormErrorWriter;
use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\Primitive;
use \Onphp\WrongStateException;

class JoinCommand
{
	use OutputMsg, FormErrorWriter {
		OutputMsg::msg insteadof FormErrorWriter;
	}

	public function run()
	{
		$form = $this->getForm()
			->import(CMDUtils::getOptionsList())
			->checkRules();

		if ($this->processFormError($form)) {
			return;
		}

		list($oldClasses, $oldFuncs, $oldConstants) = $this->readConfig($form->getValue('--old'));
		list($newClasses, $newFuncs, $newConstants) = $this->readConfig($form->getValue('--new'));
		
		$storage = new ClassStorage();
		$this->joinConstants($storage, $oldConstants, $newConstants);
		$this->joinClasses($storage, $oldClasses, $newClasses);
		
		print $storage->export()."\n";
	}
	
	private function readConfig($path)
	{
		$classes = [];
		$funcs = [];
		$constants = [];
		$data = file_get_contents($path);
		foreach (explode("\n", $data) as $line0 => $row) {
			$line = $line0 + 1;
			if (mb_strpos($row, ';') === 0 || !trim($row)) {
				continue;
			}
			$parts = explode(':', $row);
			$type = array_shift($parts);
			if (in_array($type, ['C', 'F']) && count($parts) == 2) {
				if ($type == 'F') {
					continue; //we still not work with functions
				}
				list($className, $namespace) = $parts;
				$classes[$className] = $namespace;
			} elseif ($type == 'CONST' && count($parts) == 1) {
				$constants[$parts[0]] = true;
			} else {
				throw new WrongStateException(
					"Unknown type at line {$line} , file {$path}"
				);
			}
		}
		
		return [$classes, $funcs, $constants];
	}
	
	private function joinConstants(ClassStorage $storage, array $oldConstants, array $newConstants)
	{
		foreach (array_keys($oldConstants + $newConstants) as $constant) {
			$storage->addConstant(NsConstant::create()->setName($constant));
		}
	}
	
	private function joinClasses(ClassStorage $storage, array $oldClasses, array $newClasses)
	{
		$classes = [];
		foreach ($oldClasses as $className => $oldNamespace) {
			if (isset($newClasses[$className])) {
				$classes[$className] = [$oldNamespace, $newClasses[$className]];
				unset($newClasses[$className]);
			} else {
				$classes[$className] = [$oldNamespace, $oldNamespace];
			}
		}
		foreach ($newClasses as $className => $newNamespace) {
			$classes[$className] = [$newNamespace, $newNamespace];
		}
		
		foreach ($classes as $className => $nses) {
			list($oldNs, $newNs) = $nses;
			$storage->addClass(
				NsClass::create()
					->setName($className)
					->setNamespace($oldNs)
					->setNewNamespace($newNs)
			);
		}
	}

	/**
	 * @return Form
	 */
	private function getForm()
	{
		$form = Form::create();
		$this->fillFilePathPrimitive($form, '--old');
		$this->fillFilePathPrimitive($form, '--new');
		return $form;
	}
	
	private function fillFilePathPrimitive(Form $form, $name)
	{
		$callback = function (Form $form) use ($name) {
			if ($path = $form->getValue($name)) {
				if (!file_exists($path)) {
					$form->markWrong($name, 'file not exists');
				} elseif (!is_readable($path)) {
					$form->markWrong($name, 'file not readable');
				} elseif (!is_file($path)) {
					$form->markWrong($name, 'not a file');
				}
			}
		};
		
		$form
			->add(Primitive::string($name)->required())
			->addRule($name.'Rule', CallbackLogicalObjectSuccess::create($callback));
	}
}
