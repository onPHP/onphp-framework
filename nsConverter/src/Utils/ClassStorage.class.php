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

namespace Onphp\NsConverter;

class ClassStorage
{
	private $constants = [];

	private $classStorage = [];
	private $oldNamesMap = [];
	/**
	 * @var CodeConverterAlias
	 */
	private $aliasConverter = [];

	/**
	 * @param \Onphp\NsConverter\NsConstant $constant
	 * @return \Onphp\NsConverter\ClassStorage
	 * @throws \Onphp\WrongStateException
	 */
	public function addConstant(NsConstant $constant)
	{
		if (isset($this->constants[$constant->getName()])) {
			throw new \Onphp\WrongStateException('Constant "'.$fullNewName.'" already added');
		}
		$this->constants[$constant->getName()] = $constant;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\NsClass $class
	 * @return \Onphp\NsConverter\ClassStorage
	 * @throws \Onphp\WrongStateException
	 */
	public function addClass(NsObject $class)
	{
		$fullName = $class->getFullName();
		$fullNewName = $class->getFullNewName();
		if (isset($this->classStorage[$fullNewName])) {
			$addedClass = $this->classStorage[$fullNewName];
			/* @var $addedClass \Onphp\NsConverter\NsClass */
			if (
				$addedClass->getName() == $class->getName()
				&& $addedClass->getNamespace() == $class->getNamespace()
				&& $addedClass->getNewNamespace() == $class->getNewNamespace()
			) {
				return $this;
			}
			throw new \Onphp\WrongStateException('Class name "'.$fullNewName.'" already added');
		}
		if (isset($this->oldNamesMap[$fullName])) {
			throw new \Onphp\WrongStateException('Old Class name "'.$fullName.'" already added');
		}
		$this->oldNamesMap[$fullName] = $fullNewName;
		$this->classStorage[$fullNewName] = $class;

		return $this;
	}

	public function setAliasConverter(CodeConverterAlias $aliasConverter)
	{
		$this->aliasConverter = $aliasConverter;
		return $this;
	}

	/**
	 * @param string $name
	 * @return NsConstant
	 */
	public function findConstant($name)
	{
		return isset($this->constants[$name])
			? $this->constants[$name]
			: null;
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @return \Onphp\NsConverter\NsObject
	 */
	public function findByClassNs($name, $namespace = null)
	{
		$fullName = NamespaceUtils::fixNamespace($namespace).$name;
		if (isset($this->oldNamesMap[$fullName])) {
			$fullName = $this->oldNamesMap[$fullName];
		}

		return isset($this->classStorage[$fullName])
			? $this->classStorage[$fullName]
			: null;
	}

	/**
	 * @param string $fullName
	 * @return \Onphp\NsConverter\NsObject
	 */
	public function findByFullName($fullName)
	{
		list($namespace, $name) = NamespaceUtils::explodeFullName($fullName);
		return $this->findByClassNs($name, $namespace);
	}

	/**
	 * @param string $fullName
	 * @return \Onphp\NsConverter\NsObject
	 */
	public function findByClassName($className, $currentNs, $aliases = true)
	{
		if (mb_strpos($className, '\\') !== 0) {
			if ($fullClassName = $this->aliasConverter->getAliasBuffer()->findClass($className)) {
				$className = $fullClassName;
			} elseif ($class = $this->findByClassNs($className, $currentNs)) {
				return $class;
			}
		}
		return $this->findByFullName($className);
	}

	public function getAliasClassName(NsClass $className, $newNs = null)
	{
		return $this->aliasConverter->getClassNameAlias($className->getFullNewName(), $newNs);
	}

	public function export()
	{
		$config = [];
		foreach ($this->constants as $constant) {
			/* @var $constant NsConstant */
			$config[] = implode(':', ['CONST', $constant->getName()]);
		}
		foreach ($this->classStorage as $class) {
			/* @var $class \Onphp\NsConverter\NsObject */
			$parts = [
				$class instanceof NsClass ? 'C' : 'F',
				$class->getName(),
				$class->getNamespace(),
				$class->getNewNamespace(),
			];
			$config[] = implode(':', $parts);
		}
		return implode("\n", $config);
	}

	public function import($data)
	{
		foreach (explode("\n", $data) as $line => $row) {
			if (!trim($row))
				continue;
			if (mb_strpos($row, ';') === 0)
				continue;

			$parts = explode(':', $row);
			if (count($parts) == 2 && $parts[0] == 'CONST') {
				$constant = NsConstant::create()
					->setName($parts[1]);
				$this->addConstant($constant);
			} elseif (count($parts) == 4 && in_array($parts[0], ['C', 'F'])) {
				list($type, $name, $oldNamespace, $newNamespace) = $parts;
				$object = ($type == 'C' ? NsClass::create() : NsFunction::create());
				/* @var $object NsObject */
				$class = $object
					->setName($name)
					->setNamespace($oldNamespace)
					->setNewNamespace($newNamespace);
				$this->addClass($class);
			} else {
				throw new \Onphp\WrongStateException("Undefined row at line {$line}: {$row}");
			}
		}
	}
}
