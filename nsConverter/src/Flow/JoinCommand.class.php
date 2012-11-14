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

use \Onphp\Assert;
use \Onphp\Form;
use \Onphp\NsConverter\AddUtils\CallbackLogicalObjectSuccess;
use \Onphp\NsConverter\AddUtils\CMDUtils;
use \Onphp\NsConverter\AddUtils\ConsoleValueSelector;
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

		$oldStorage = $this->readConfig($form->getValue('--old'));
		$newStorage = $this->readConfig($form->getValue('--new'));
		
		$storage = $this->join($oldStorage, $newStorage);
		
		print $storage->export()."\n";
	}
	
	private function readConfig($path)
	{
		$storage = new ClassStorage();
		
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
				$class = NsClass::create()
					->setName($className)
					->setNamespace($namespace)
					->setNewNamespace($namespace);
				$storage->addClass($class);
			} elseif ($type == 'CONST' && count($parts) == 1) {
				$storage->addConstant(NsConstant::create()->setName($parts[0]));
			} else {
				throw new WrongStateException(
					"Unknown type at line {$line} , file {$path}"
				);
			}
		}
		
		return $storage;
	}
	
	/**
	 * @param ClassStorage $oldStorage
	 * @param ClassStorage $newStorage
	 * @return ClassStorage
	 */
	private function join(ClassStorage $oldStorage, ClassStorage $newStorage)
	{
		$storage = new ClassStorage();
		$this->joinConstants($storage, $oldStorage, $newStorage);
		$this->joinClasses($storage, $oldStorage, $newStorage);
		
		return $storage;
	}
	
	private function joinConstants(ClassStorage $storage, ClassStorage $oldStorage, ClassStorage $newStorage)
	{
		foreach ($oldStorage->getListConstants() as $constant) {
			$storage->addConstant($constant);
		}
		
		foreach ($newStorage->getListConstants() as $constant) {
			try {
				$storage->addConstant($constant);
			} catch (WrongStateException $e) {
				/* do nothing */
			}
		}
	}
	
	private function joinClasses(ClassStorage $storage, ClassStorage $oldStorage, ClassStorage $newStorage)
	{
		foreach ($oldStorage->getListClasses() as $oldClass) {
			$oldStorage->dropClass($oldClass);
			/* @var $oldClass NsClass */
			if ($newClass = $newStorage->findByFullName($oldClass->getFullName())) {
				$newStorage->dropClass($newClass);
				$this->joinClass($storage, $oldClass, $newClass);
				continue;
			}
			
			$possibleList = $newStorage->getListByOldClassName($oldClass->getName());
			if (count($possibleList) == 0) {
				$storage->addClass($oldClass);
			} elseif (count($possibleList) == 1) {
				$newStorage->dropClass($possibleList[0]);
				$this->joinClass($storage, $oldClass, $possibleList[0]);
			} else {
				$this->msg("For old class {$oldClass->getFullName()} found some new names:");
				$list = [];
				foreach ($possibleList as $newClass) {
					/* @var $newClass NsClass */
					$list[] = $newClass->getFullNewName();
				}
				$selector = new ConsoleValueSelector();
				$fullNewClassName = $selector->setList($list)->readValue();
				foreach ($possibleList as $newClass) {
					/* @var $newClass NsClass */
					if ($fullNewClassName == $newClass->getFullNewName()) {
						$newStorage->dropClass($newClass);
						$this->joinClass($storage, $oldClass, $newClass);
						break 2;
					}
				}
				Assert::isUnreachable();
			}
		}
		
		foreach ($newStorage->getListClasses() as $newClass) {
			$storage->addClass($newClass);
		}
		
		return $storage;
	}
	
	private function joinClass(ClassStorage $storage, NsClass $oldClass, NsClass $newClass)
	{
		$class = NsClass::create()
			->setName($oldClass->getName())
			->setNamespace($oldClass->getNamespace())
			->setNewNamespace($newClass->getNewNamespace());
		$storage->addClass($class);
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
